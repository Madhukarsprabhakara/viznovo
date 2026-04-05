<?php

namespace App\Http\Controllers;

use App\Ai\Agents\AnalysisPlanning;
use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Str;
use App\Services\AIService;
use App\Services\CsvDataSourceService;
use App\Services\CsvDTTableService;
use App\Services\ProjectDataMetricsService;
use App\Models\AIModel;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Ai\Agents\DiscoverFiles;
use App\Ai\Agents\CreatePrompt5dImpact;
use App\Ai\Agents\CustomResearch;
use App\Ai\Agents\MetricsDiscovery;
use App\Ai\Agents\ManualModeMetricsDiscovery;
use App\Ai\Agents\ManualModeQualitativeDataInsights;
use App\Ai\Agents\PromptDesigner;
use App\Ai\Agents\CreateDashboard;
use App\Ai\Agents\QualitativeDataInsights;
use App\Services\QdaService;
use App\Jobs\ManualModeMetricsDiscoveryJ;
use App\Jobs\ManualModeQualitativeDataInsightsJ;
use App\Jobs\CreateDashboardJ;
use App\Jobs\IdentifyMetricsAndDerivedTableColumns;
use App\Services\DispatchJobsService;
use Spatie\Browsershot\Browsershot;
use App\Events\ReportStatusUpdate;
use App\Jobs\DispatchDerivedTableJobs;
use App\Ai\Agents\CompleteDataSetCreation;
use App\Jobs\ExecuteDerivedMetrics;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    private function extractPromptResponse(mixed $decoded, string $rawText): ?string
    {
        if (is_array($decoded)) {
            if (array_key_exists('prompt_response', $decoded) && is_string($decoded['prompt_response'])) {
                return $decoded['prompt_response'];
            }

            if (array_is_list($decoded)) {
                foreach ($decoded as $item) {
                    if (is_array($item) && array_key_exists('prompt_response', $item) && is_string($item['prompt_response'])) {
                        return $item['prompt_response'];
                    }
                }
            }
        }

        $trimmed = trim($rawText);

        // If the model returned raw HTML (full doc or fragment), accept it.
        if (stripos($trimmed, '<html') !== false || stripos($trimmed, '<!doctype html') !== false) {
            return $trimmed;
        }

        $firstTagPos = strpos($trimmed, '<');
        $lastTagPos = strrpos($trimmed, '>');
        if ($firstTagPos !== false && $lastTagPos !== false && $lastTagPos > $firstTagPos) {
            $possibleHtml = trim(substr($trimmed, $firstTagPos, $lastTagPos - $firstTagPos + 1));
            if ($possibleHtml !== '' && preg_match('/^\s*</', $possibleHtml) === 1) {
                // Heuristic: if it ends with a closing tag or contains a div root, it's likely HTML.
                if (preg_match('/<\/[a-zA-Z][^>]*>\s*$/', $possibleHtml) === 1 || stripos($possibleHtml, '<div') !== false) {
                    return $possibleHtml;
                }
            }
        }

        return null;
    }

    private function decodeAiJson(string $rawText): array
    {
        $trimmed = trim($rawText);

        $candidates = [];
        $seen = [];
        $addCandidate = function (mixed $value) use (&$candidates, &$seen): void {
            $value = trim((string) $value);
            if ($value === '') {
                return;
            }
            if (isset($seen[$value])) {
                return;
            }
            $seen[$value] = true;
            $candidates[] = $value;
        };

        $addCandidate($trimmed);

        // Strip common code fences.
        $noFences = preg_replace('/^\s*```(?:json)?\s*/i', '', $trimmed);
        $noFences = preg_replace('/\s*```\s*$/', '', (string) $noFences);
        $noFences = trim((string) $noFences);
        $addCandidate($noFences);

        // Sometimes the whole payload is wrapped in quotes.
        foreach ([$trimmed, $noFences] as $v) {
            $v = trim((string) $v);
            if (strlen($v) >= 2 && ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'")))) {
                $addCandidate(substr($v, 1, -1));
            }
        }

        // Extract a likely JSON object/array from mixed content (e.g. trailing error text).
        foreach ([$trimmed, $noFences] as $v) {
            $v = (string) $v;

            $objStart = strpos($v, '{');
            $objEnd = strrpos($v, '}');
            if ($objStart !== false && $objEnd !== false && $objEnd > $objStart) {
                $addCandidate(substr($v, $objStart, $objEnd - $objStart + 1));
            }

            $arrStart = strpos($v, '[');
            $arrEnd = strrpos($v, ']');
            if ($arrStart !== false && $arrEnd !== false && $arrEnd > $arrStart) {
                $addCandidate(substr($v, $arrStart, $arrEnd - $arrStart + 1));
            }
        }

        // Attempt to repair the common invalid pattern: [{""prompt_response"":""...""}]
        foreach ($candidates as $candidate) {
            if (str_contains($candidate, '""')) {
                $addCandidate(str_replace('""', '"', $candidate));
            }
        }

        $lastError = null;

        // Try decoding each candidate.
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }

            $decoded = json_decode($candidate, true);
            $error = json_last_error();
            $errorMessage = json_last_error_msg();
            $lastError = $errorMessage;

            if ($error !== JSON_ERROR_NONE) {
                continue;
            }

            // Some providers / gateways may return JSON as a quoted string (double-encoded).
            if (is_string($decoded)) {
                $decoded2 = json_decode($decoded, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [$decoded2, null];
                }
            }

            // Sometimes JSON is returned as a list of JSON strings: ["{...}"]
            if (is_array($decoded) && array_is_list($decoded)) {
                foreach ($decoded as $item) {
                    if (!is_string($item)) {
                        continue;
                    }

                    $itemDecoded = json_decode($item, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return [$itemDecoded, null];
                    }
                }
            }

            return [$decoded, null];
        }

        // Last error message based on raw trimmed input.
        json_decode($trimmed, true);
        return [null, $lastError ?: (json_last_error_msg() ?: 'Invalid JSON')];
    }

    private function resolveChromeExecutablePath(): ?string
    {
        $candidates = [
            env('BROWSERSHOT_CHROME_PATH'),
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/google-chrome',
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || $candidate === '') {
                continue;
            }
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveNodeBinaryPath(): ?string
    {
        $candidates = [
            env('BROWSERSHOT_NODE_BINARY'),
            '/usr/bin/node',
            '/usr/local/bin/node',
            '/opt/homebrew/bin/node',
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || $candidate === '') {
                continue;
            }
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveNpmBinaryPath(): ?string
    {
        $candidates = [
            env('BROWSERSHOT_NPM_BINARY'),
            '/usr/bin/npm',
            '/usr/local/bin/npm',
            '/opt/homebrew/bin/npm',
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || $candidate === '') {
                continue;
            }
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function makeReportDownloadFilename(Report $report): string
    {
        $title = Str::slug($report->title ?: 'report');

        return ($title !== '' ? $title : 'report') . '.pdf';
    }

    private function createReportBrowsershot(
        string $html,
        bool $singlePage = false,
        int $timeout = 90,
        int $protocolTimeout = 120,
        string $waitUntil = 'networkidle0'
    ): Browsershot
    {
        $browsershot = Browsershot::html($html)
            ->showBackground()
            ->emulateMedia('print')
            ->windowSize(1440, $singlePage ? 2200 : 1600)
            ->timeout($timeout)
            ->setOption('protocolTimeout', $protocolTimeout * 1000)
            ->noSandbox();

        if ($waitUntil === 'networkidle0') {
            $browsershot->waitUntilNetworkIdle();
        } else {
            $browsershot->setOption('waitUntil', $waitUntil);
        }

        $nodeBinary = $this->resolveNodeBinaryPath();
        if ($nodeBinary) {
            $browsershot->setNodeBinary($nodeBinary);
        }

        $npmBinary = $this->resolveNpmBinaryPath();
        if ($npmBinary) {
            $browsershot->setNpmBinary($npmBinary);
        }

        $chromePath = $this->resolveChromeExecutablePath();
        if ($chromePath) {
            $browsershot->setChromePath($chromePath);
        }

        return $browsershot;
    }

    private function resolveReportPdfDimensions(string $html, string $waitUntil = 'networkidle0'): array
    {
        $defaultWidthMm = (float) env('BROWSERSHOT_SINGLE_PAGE_WIDTH_MM', 210);
        $defaultHeightMm = (float) env('BROWSERSHOT_SINGLE_PAGE_HEIGHT_MM', 2000);

        try {
            $result = $this->createReportBrowsershot(
                $html,
                true,
                (int) env('BROWSERSHOT_DIMENSION_TIMEOUT', 60),
                (int) env('BROWSERSHOT_DIMENSION_PROTOCOL_TIMEOUT', 120),
                $waitUntil
            )->evaluate(<<<'JS'
(async () => {
    const root = document.querySelector('.report-content') || document.body;
    const elements = Array.from(root.querySelectorAll('*'));
    const rects = [root, ...elements]
        .map((element) => element.getBoundingClientRect())
        .filter((rect) => rect.width > 0 && rect.height > 0);

    if (rects.length === 0) {
        return JSON.stringify({
            width: Math.max(document.documentElement.scrollWidth, document.body.scrollWidth, 794),
            height: Math.max(document.documentElement.scrollHeight, document.body.scrollHeight, 1123),
        });
    }

    const left = Math.min(...rects.map((rect) => rect.left));
    const right = Math.max(...rects.map((rect) => rect.right));
    const top = Math.min(...rects.map((rect) => rect.top));
    const bottom = Math.max(...rects.map((rect) => rect.bottom));

    return JSON.stringify({
        width: Math.max(right - left, 794),
        height: Math.max(bottom - top, 1123),
    });
})()
JS);

            if (is_string($result)) {
                $decoded = json_decode($result, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $result = $decoded;
                }
            }
            if (!is_array($result)) {
                return ['width' => $defaultWidthMm, 'height' => $defaultHeightMm];
            }

            $pxWidth = max((float) ($result['width'] ?? 0), 794.0);
            $pxHeight = max((float) ($result['height'] ?? 0), 1123.0);
            $heightScale = (float) env('BROWSERSHOT_HEIGHT_SCALE', 1.02);

            return [
                'width' => max(round($pxWidth * 25.4 / 96, 2), $defaultWidthMm),
                'height' => max(round(($pxHeight * $heightScale) * 25.4 / 96, 2), 297.0),
            ];
        } catch (\Throwable $e) {
            return ['width' => $defaultWidthMm, 'height' => $defaultHeightMm];
        }
    }

    private function isFullHtmlDocument(string $html): bool
    {
        $trimmed = ltrim($html);

        return str_starts_with(strtolower($trimmed), '<!doctype html')
            || (stripos($trimmed, '<html') !== false && stripos($trimmed, '<body') !== false);
    }

    private function pdfDocumentStyleBlock(): string
    {
        return <<<'HTML'
<style>
  :root {
    color-scheme: light;
  }

    html,
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

  @page {
        margin: 0;
  }

  @media print {
    html,
    body {
      color: #0f172a !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body {
            padding: 12mm 10mm !important;
            box-sizing: border-box !important;
    }

    [class*="backdrop-blur"] {
      -webkit-backdrop-filter: none !important;
      backdrop-filter: none !important;
    }

        [data-lucide] {
            display: none !important;
        }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    p,
    span,
    li,
    td,
    th {
      color: #0f172a !important;
      opacity: 1 !important;
    }
  }
</style>
HTML;
    }

    private function sanitizeFullDocumentForPdf(string $html): string
    {
        $sanitized = preg_replace_callback(
            '/<script\b[^>]*(?:src=["\']([^"\']+)["\'])?[^>]*>.*?<\/script>/is',
            static function (array $matches): string {
                $src = $matches[1] ?? '';

                if (is_string($src) && preg_match('/cdn\.tailwindcss\.com/i', $src) === 1) {
                    return $matches[0];
                }

                return '';
            },
            $html
        ) ?? $html;

        return preg_replace('/\sdata-lucide="[^"]*"/i', '', $sanitized) ?? $sanitized;
    }

    private function shouldInjectTailwindCompatibility(string $html): bool
    {
        if (preg_match('/cdn\.tailwindcss\.com/i', $html) === 1) {
            return false;
        }

        $usesTailwindV3Palette = preg_match('/(?:slate|rose|sky)-\d{2,3}/i', $html) === 1;
        $referencesTailwindV2 = preg_match('/tailwindcss@2\.[^\/]*/i', $html) === 1;

        return $usesTailwindV3Palette || $referencesTailwindV2;
    }

    private function injectTailwindCompatibility(string $html): string
    {
        if (! $this->shouldInjectTailwindCompatibility($html)) {
            return $html;
        }

        return $this->injectIntoHtmlHead(
            $html,
            '<script src="https://cdn.tailwindcss.com?plugins=typography"></script>'
        );
    }

    private function injectIntoHtmlHead(string $html, string $injection): string
    {
        if (stripos($html, '</head>') !== false) {
            return preg_replace('/<\/head>/i', $injection . PHP_EOL . '</head>', $html, 1) ?? ($html . $injection);
        }

        if (stripos($html, '<html') !== false) {
            return preg_replace('/<html[^>]*>/i', '$0<head>' . $injection . '</head>', $html, 1) ?? ($html . $injection);
        }

        return $injection . $html;
    }

    private function renderReportHtml(Report $report, bool $isPdfExport): string
    {
        $content = (string) $report->result;

        if ($this->isFullHtmlDocument($content)) {
            if (! $isPdfExport) {
                return $this->injectTailwindCompatibility($content);
            }

            return $this->injectIntoHtmlHead(
                $this->injectTailwindCompatibility($this->sanitizeFullDocumentForPdf($content)),
                $this->pdfDocumentStyleBlock()
            );
        }

        return view('Global.public_report', [
            'content' => $content,
            'report' => $report,
            'isPdfExport' => $isPdfExport,
        ])->render();
    }

    private function renderReportPdf(string $html): string
    {
        if ($this->isFullHtmlDocument($html)) {
            $dimensions = $this->resolveReportPdfDimensions($html, 'load');

            return $this->createReportBrowsershot(
                $html,
                true,
                (int) env('BROWSERSHOT_FULL_DOCUMENT_TIMEOUT', 120),
                (int) env('BROWSERSHOT_FULL_DOCUMENT_PROTOCOL_TIMEOUT', 180),
                'load'
            )
                ->paperSize($dimensions['width'], $dimensions['height'], 'mm')
                ->margins(0, 0, 0, 0)
                ->setOption('preferCSSPageSize', true)
                ->pdf();
        }

        $dimensions = $this->resolveReportPdfDimensions($html);

        return $this->createReportBrowsershot(
            $html,
            true,
            (int) env('BROWSERSHOT_SINGLE_PAGE_TIMEOUT', 120),
            (int) env('BROWSERSHOT_SINGLE_PAGE_PROTOCOL_TIMEOUT', 180)
        )
            ->paperSize($dimensions['width'], $dimensions['height'], 'mm')
            ->margins(0, 0, 0, 0)
            ->setOption('preferCSSPageSize', true)
            ->pdf();
    }

    public function downloadPdf($uuid)
    {
        $report = Report::where('uuid', $uuid)->firstOrFail();

        try {
            $html = $this->renderReportHtml($report, true);
            $pdf = $this->renderReportPdf($html);

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $this->makeReportDownloadFilename($report) . '"',
            ]);
        } catch (\Throwable $e) {
            Log::error('Report PDF download failed.', [
                'uuid' => $uuid,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response('Failed to generate report PDF.', 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        //
        try {

            return Inertia::render('Reports/Show', [
                'project' => $project,
                'reports' => $project->reports,

            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load project: ' . $e->getMessage()])->withInput();
        }
    }

    public function createForm(Project $project)
    {
        try {
            $aiModels = new AIModel();
            return Inertia::render('Reports/Create', [
                'project' => $project,
                'reports' => $project->reports,
                'aiModels' => $aiModels->getModels(),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load project: ' . $e->getMessage()])->withInput();
        }
    }
    public function autoCreateForm(Project $project)
    {
        try {
            $aiModels = new AIModel();
            return Inertia::render('Reports/AutoCreate', [
                'project' => $project,
                'reports' => $project->reports,
                'aiModels' => $aiModels->getModels(),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load project: ' . $e->getMessage()])->withInput();
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function batch(Request $request, Project $project, AIService $aiService)
    {
        //
        try {
            //access the csv
            //get 500 rows at a time
            //create batch jsonl file
            //send to openai
            //store results
            //take individual results and consolidate into final report using openai
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function autoCreate(Request $request, Project $project, ProjectDataMetricsService $projectDataMetricsService)
    {
        //
        try {
            $request->validate([
                'model_key' => 'required|string',
                'title' => 'nullable|string|max:255',
                'report_id' => 'nullable|integer|exists:reports,id',
            ]);

            $userId = Auth::id();

            $report = null;
            if ($request->filled('report_id')) {
                $report = Report::where('id', $request->integer('report_id'))
                    ->where('user_id', $userId)
                    ->where('project_id', $project->id)
                    ->first();

                if (!$report) {
                    return response()->json(['message' => 'Invalid report_id.'], 403);
                }

                $report->update([
                    'model_key' => $request->model_key,
                    'is_automatic' => true,
                ]);
            }

            if (!$report) {
                $fallbackTitle = trim((string) ($request->input('title') ?: 'Auto dashboard'));
                $fallbackPrompt = (string) ($request->input('prompt') ?: ($request->input('template_id') ?: ''));

                $report = Report::create([
                    'user_id' => $userId,
                    'uuid' => (string) Str::uuid(),
                    'title' => $fallbackTitle !== '' ? $fallbackTitle : 'Auto dashboard',
                    'project_id' => $project->id,
                    'prompt' => $fallbackPrompt,
                    'result' => null,
                    'is_automatic' => true,
                    'model_key' => $request->model_key,
                ]);
            }

            // return $request->all();
            // sleep(10); // Simulate a delay for processing
            //get all pdf data for the project
            //extract text from the pdfs
            //create a json response from extracted text
            //send the json data along with the prompt to open ai
            //get the response from openai
            //store it in reports table
            // Get all PDF files for the project
            $allFiles = $project->files;
            // $allFiles = $project->files()->where('type', 'application/pdf')->get();

            $pdfContentArr = [];
            $csvContentArr = [];
            $pgsqlContentArr = [];
            $pgsqlFinalArr = [];
            $pgsqlOpenEndedArr = [];
            $pgsqlOpenEndedFinalArr = [];
            $websiteContentArr = [];
            $metricSqls = [];
            $project_data_ids = [];
            foreach ($allFiles as $file) {
                if ($file->type === 'application/pdf') {
                    // Adjust the disk and path as per your storage setup
                    $filePath = storage_path('app/private/' . $file->url);

                    $content = '';
                    try {
                        $content = Str::of(Pdf::getText($filePath))
                            ->split("/\f/")
                            ->toArray();
                    } catch (\Exception $e) {
                        $content = 'Could not extract text: ' . $e->getMessage();
                    }

                    $pdfContentArr[] = [
                        'filename' => $file->name ?? basename($file->system_name),
                        'pdf_content' => $content,
                    ];
                }
                if ($file->type === 'text/csv') {

                    if ($file->is_csv_data_type_table_populated) {
                        // return $project->schema_name.$file->csv_data_type_table_name;
                        $csvDTTableService = new CsvDTTableService();
                        $pgsqlContentArr['project_id'] = $project->id;
                        $pgsqlContentArr['project_data_id'] = $file->id;
                        $pgsqlContentArr['user_id'] = $file->user_id;
                        $pgsqlContentArr['schema_name'] = $project->schema_name;
                        $pgsqlContentArr['table_name'] = $file->csv_data_type_table_name;
                        $pgsqlContentArr['table_schema'] = $file->projectDataCsvs;
                        $pgsqlContentArr['records'] = $csvDTTableService->getDataTypeTableRecords($project->schema_name, $file->csv_data_type_table_name);
                        $pgsqlOpenEndedArr['schema_name'] = $project->schema_name;
                        $pgsqlOpenEndedArr['table_name'] = $file->csv_data_type_table_name;
                        $pgsqlOpenEndedArr['open_ended_responses'] = $csvDTTableService->getOpenEndedResponsesForIncrementalAnalysis($file, $project->schema_name, $file->csv_data_type_table_name);
                        $pgsqlFinalArr[] = $pgsqlContentArr;
                        $pgsqlOpenEndedFinalArr[] = $pgsqlOpenEndedArr;
                    } else {
                        $csvDataSourceService = new CsvDataSourceService();
                        $csvContentArr[] = $csvDataSourceService->getDataFromCsvforDashboardCreate($file);
                    }
                }

                if ($file->type === 'website') {
                    $websiteItem = [
                        'website_url' => $file->url,
                    ];

                    try {
                        $chromePath = $this->resolveChromeExecutablePath();

                        $timeoutSeconds = (int) env('BROWSERSHOT_TIMEOUT', 90);
                        $delayMs = (int) env('BROWSERSHOT_JS_DELAY_MS', 2000);

                        $browsershot = Browsershot::url($file->url)
                            ->noSandbox()
                            ->setNodeBinary(env('BROWSERSHOT_NODE_BINARY', '/usr/bin/node'))
                            ->setNpmBinary(env('BROWSERSHOT_NPM_BINARY', '/usr/bin/npm'))
                            ->timeout($timeoutSeconds)
                            ->waitUntilNetworkIdle()
                            ->setDelay($delayMs)
                            ->setNodeEnv([
                                'HOME' => '/tmp',
                                'XDG_CACHE_HOME' => '/tmp',
                                'PUPPETEER_CACHE_DIR' => '/tmp/puppeteer',
                            ]);

                        if ($chromePath) {
                            $browsershot->setChromePath($chromePath);
                        }

                        $websiteItem['website_html'] = $browsershot->bodyHtml();
                    } catch (\Throwable $e) {
                        $websiteItem['website_error'] = $e->getMessage();
                    }

                    $websiteContentArr[] = $websiteItem;
                }
            }




            $input_data = [
                'pdf_content' => $pdfContentArr,
                'csv_content' => $csvContentArr,
                'website_urls' => $websiteContentArr,
                'pgsql_tables' => $pgsqlFinalArr,
            ];
            $qda = [
                'pdf_content' => $pdfContentArr,
                'website_urls' => $websiteContentArr,
                'open_ended_responses' => $pgsqlOpenEndedFinalArr,
            ];

            $jsonQda = json_encode($qda);
            $jsonData = json_encode($input_data);
            // File discovery
            // create dashboard based on the insights from file discovery agent


            if ($request->model_key == 'gpt-5') {
                $discovery = (new DiscoverFiles)->forUser($request->user())
                    ->prompt(
                        'Here are the files and its contents...\n\n' . $jsonData,
                        provider: [
                            'openai' => 'gpt-5.2',
                            'gemini' => 'gemini-3.1-pro-preview',
                        ],
                        timeout: 600,
                    );

                $discovery_string = (string) $discovery;


                $analysisPlan = (new AnalysisPlanning)->forUser($request->user())
                    ->prompt(
                        'Here are the summaries of the data sources...\n\n' . $discovery_string . '\n\n Here is the sample data from the sources...' . $jsonData,
                        provider: [
                            'openai' => 'gpt-5.2',
                            'gemini' => 'gemini-3.1-pro-preview',
                        ],
                        timeout: 600,
                    );

                $analysisPlanString = (string) $analysisPlan;

                foreach ($input_data['pgsql_tables'] as $tableData) {
                    $tableDataString = json_encode($tableData);
                    $metrics_sql = (new MetricsDiscovery)->forUser($request->user())
                        ->prompt(
                            'Here is the data analysis plan...\n\n' . $analysisPlanString . '\n\n Here is the sample data and the postgres table schema from the sources...' . $tableDataString,
                            provider: [
                                'openai' => 'gpt-5.2',
                                'gemini' => 'gemini-3.1-pro-preview',
                            ],
                            timeout: 600,
                        );
                    $metrics_sql_string = (string) $metrics_sql;
                    [$promptDecoded, $promptDecodeError] = $this->decodeAiJson($metrics_sql_string);
                    $promptDecoded['project_id'] = $project->id;
                    $promptDecoded['project_data_id'] = $tableData['project_data_id'] ?? null;
                    $promptDecoded['user_id'] = $tableData['user_id'] ?? null;
                    $metricSqls[] = $promptDecoded ?? null;
                    // You can further process each table's data here if needed
                    // For example, you might want to summarize the schema or sample records
                    $project_data_ids[] = $tableData['project_data_id'] ?? null;
                }

                $sqls = $projectDataMetricsService->store($metricSqls, $userId, $report->id);

                //Qualitative data analytics

                $qdaInsights = (new QualitativeDataInsights)->forUser($request->user())
                    ->prompt(
                        'Here is all of the qualitative data gathered so far...\n\n' .  $jsonQda,
                        provider: [
                            'openai' => 'gpt-5.2',
                            'gemini' => 'gemini-3.1-pro-preview',
                        ],
                        timeout: 600,
                    );

                $qdaInsightsString = (string) $qdaInsights;

                $qdaInsightsDecoded = json_decode($qdaInsightsString, true);

                // $qualitative_data['pdf_content'] = $input_data['pdf_content'];
                // $qualitative_data['website_urls'] = $input_data['website_urls'];
                // $qualitative_data['open_ended_responses'] = null;
                $discovery_array = json_decode($discovery_string, true);
                $analysisPlanArray = json_decode($analysisPlanString, true);
                $data_for_prompt_design = [
                    // 'analysis_plan' => $analysisPlanArray['analysis_plan'] ?? null,
                    'datasource_summary' => $discovery_array['summary_insights'] ?? null,
                    'metrics_insights' => $projectDataMetricsService->getDataForPromptDesign($report->id),
                    'qualitative_data_insights' => $qdaInsightsDecoded['qualitative_insights'] ?? null,
                ];
                //loop through the pgsql tables and for each table ask the model to generate metrics and sql query

                $prompt_designed = (new PromptDesigner)->forUser($request->user())
                    ->prompt(
                        'Here is all of the information gathered so far...\n\n' . json_encode($data_for_prompt_design),
                        provider: [
                            'openai' => 'gpt-5.2',
                            'gemini' => 'gemini-3.1-pro-preview',
                        ],
                        timeout: 600,
                    );

                $promptDesignedString = (string) $prompt_designed;

                $rawPromptDd = $promptDesignedString;
                [$promptDecoded, $promptDecodeError] = $this->decodeAiJson($rawPromptDd);
                $nextAgentPrompt = is_array($promptDecoded) ? ($promptDecoded['next_agent_prompt'] ?? null) : null;

                $prompt = $nextAgentPrompt;

                $response = (new CreateDashboard)->forUser($request->user())
                    ->prompt(
                        'Here are the instructions...\n\n' . $prompt . ' and the insights:' . json_encode($data_for_prompt_design),
                        provider: [
                            'openai' => 'gpt-5.2',
                            'gemini' => 'gemini-3.1-pro-preview',
                        ],
                        timeout: 600,
                    );
            }
            if ($request->model_key == 'gemini-3-pro') {
                $discovery = (new DiscoverFiles)->forUser($request->user())
                    ->prompt(
                        'Here are the files and its contents...\n\n' . $jsonData,
                        provider: [
                            'gemini' => 'gemini-3.1-pro-preview',
                            'openai' => 'gpt-5.2',
                        ],
                        timeout: 600,
                    );

                $discovery_string = (string) $discovery;
                sleep(60);
                $prompt_dd = (new CreatePrompt5dImpact)->forUser($request->user())
                    ->prompt(
                        'Here are the summary of the file and url contents...\n\n' . $discovery_string,
                        provider: [
                            'gemini' => 'gemini-3.1-pro-preview',
                            'openai' => 'gpt-5.2',
                        ],
                        timeout: 600,
                    );
                $rawPromptDd = (string) $prompt_dd;
                [$promptDecoded, $promptDecodeError] = $this->decodeAiJson($rawPromptDd);
                $nextAgentPrompt = is_array($promptDecoded) ? ($promptDecoded['next_agent_prompt'] ?? null) : null;

                $prompt = $nextAgentPrompt;
                sleep(60); // Simulate a delay for processing
                $response = (new CustomResearch)->forUser($request->user())
                    ->prompt(
                        'Here are the instructions...\n\n' . $prompt . ' and the data:' . $jsonData,
                        provider: [
                            'gemini' => 'gemini-3.1-pro-preview',
                            'openai' => 'gpt-5.2',
                        ],
                        timeout: 600,
                    );
            }

            $rawResponseText = (string) $response;
            [$decoded, $decodeError] = $this->decodeAiJson($rawResponseText);
            $promptResponse = $this->extractPromptResponse($decoded, $rawResponseText);
            // return [
            //     'status' => 'success',
            //     'message' => 'Response generated successfully.',
            //     'data' => $promptResponse,
            // ];

            if (!$nextAgentPrompt) {
                return response()->json([
                    'message' => 'File discovery agent did not return next_agent_prompt',
                    'raw_response' => $response,
                ], 422);
            }

            $result = $promptResponse;



            if ($result === null) {
                return response()->json([
                    'message' => 'Report could not be generated (AI response could not be parsed). Please try re-running the report.',
                    'model_key' => $request->model_key,
                    'next_agent_prompt' => $nextAgentPrompt,
                    'decode_error' => $decodeError,
                    'raw_response_preview' => Str::limit((string) $rawResponseText, 4000),
                ], 422);
            }

            $report->update([
                'title' => (string) ($request->input('title') ?: $report->title),
                'prompt' => (string) ($nextAgentPrompt ?: $report->prompt),
                'result' => $result,
                'is_automatic' => true,
                'model_key' => $request->model_key,
            ]);

            return response()->json([
                'report_id' => $report->id,
                'report_uuid' => $report->uuid,
                'next_agent_prompt' => $nextAgentPrompt,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function create(Request $request, Project $project, ProjectDataMetricsService $projectDataMetricsService, CsvDTTableService $csvDTTableService, QdaService $qdaService)
    {
        //
        try {

            $request->validate([
                'title' => 'required|string|max:255',
                'prompt' => 'required|string',
                'model_key' => 'required|string',
                'report_id' => 'nullable|integer|exists:reports,id',
            ]);

            $userId = Auth::id();

            $report = null;
            if ($request->filled('report_id')) {
                $report = Report::where('id', $request->integer('report_id'))
                    ->where('user_id', $userId)
                    ->where('project_id', $project->id)
                    ->first();

                if (!$report) {
                    return response()->json(['message' => 'Invalid report_id.'], 403);
                }

                $report->update([
                    'title' => $request->title,
                    'prompt' => $request->prompt,
                    'model_key' => $request->model_key,
                    'is_automatic' => false,
                    'start_epoch' => now()->timestamp,
                ]);
            }

            if (!$report) {
                $report = Report::create([
                    'user_id' => $userId,
                    'uuid' => (string) Str::uuid(),
                    'title' => $request->title,
                    'project_id' => $project->id,
                    'prompt' => $request->prompt,
                    'result' => null,
                    'is_automatic' => false,
                    'model_key' => $request->model_key,
                    'start_epoch' => now()->timestamp,
                ]);
            }


            $allFiles = $project->files;
            // $allFiles = $project->files()->where('type', 'application/pdf')->get();
            $result = null;
            $pdfContentArr = [];
            $csvContentArr = [];
            $pgsqlContentArr = [];
            $pgsqlFinalArr = [];
            $pgsqlOpenEndedArr = [];
            $pgsqlOpenEndedFinalArr = [];
            $websiteContentArr = [];
            $metricSqls = [];
            $project_data_ids = [];
            foreach ($allFiles as $file) {
                if ($file->type === 'application/pdf') {
                    // Adjust the disk and path as per your storage setup
                    $filePath = storage_path('app/private/' . $file->url);

                    $content = '';
                    try {
                        $content = Str::of(Pdf::getText($filePath))
                            ->split("/\f/")
                            ->toArray();
                    } catch (\Exception $e) {
                        $content = 'Could not extract text: ' . $e->getMessage();
                    }

                    $pdfContentArr[] = [
                        'filename' => $file->name ?? basename($file->system_name),
                        'pdf_content' => $content,
                    ];
                }
                if ($file->type === 'text/csv') {
                    if ($file->is_csv_data_type_table_populated) {


                        // return $project->schema_name.$file->csv_data_type_table_name;
                        // $csvDTTableService = new CsvDTTableService();

                        // return $csvDTTableService->getDataTypeTableRecords($project->schema_name, $file->csv_data_type_table_name);
                        $pgsqlContentArr['project_id'] = $project->id;
                        $pgsqlContentArr['project_data_id'] = $file->id;
                        $pgsqlContentArr['user_id'] = $file->user_id;
                        $pgsqlContentArr['schema_name'] = $project->schema_name;
                        $pgsqlContentArr['table_name'] = $file->csv_data_type_table_name;
                        $pgsqlContentArr['derived_table_name'] = $file->csv_derived_table_name;
                        $pgsqlContentArr['table_schema'] = $file->projectDataCsvs;
                        $pgsqlContentArr['records'] = $csvDTTableService->getDataTypeTableRecords($project->schema_name, $file->csv_data_type_table_name);
                        $pgsqlOpenEndedArr['schema_name'] = $project->schema_name;
                        $pgsqlOpenEndedArr['table_name'] = $file->csv_data_type_table_name;
                        $pgsqlOpenEndedArr['derived_table_name'] = $file->csv_derived_table_name;
                        // $pgsqlOpenEndedArr['open_ended_responses'] = $csvDTTableService->getRecordsFromOpenEndedColumns($project);
                        $pgsqlFinalArr[] = $pgsqlContentArr;
                        // $pgsqlOpenEndedFinalArr[] = $pgsqlOpenEndedArr;
                    } else {
                        $csvDataSourceService = new CsvDataSourceService();
                        $csvContentArr[] = $csvDataSourceService->getDataFromCsvforDashboardCreate($file);
                    }
                }

                if ($file->type === 'website') {
                    $websiteItem = [
                        'website_url' => $file->url,
                    ];

                    try {
                        $chromePath = $this->resolveChromeExecutablePath();

                        $timeoutSeconds = (int) env('BROWSERSHOT_TIMEOUT', 90);
                        $delayMs = (int) env('BROWSERSHOT_JS_DELAY_MS', 2000);

                        $browsershot = Browsershot::url($file->url)
                            ->noSandbox()
                            ->setNodeBinary(env('BROWSERSHOT_NODE_BINARY', '/usr/bin/node'))
                            ->setNpmBinary(env('BROWSERSHOT_NPM_BINARY', '/usr/bin/npm'))
                            ->timeout($timeoutSeconds)
                            ->waitUntilNetworkIdle()
                            ->setDelay($delayMs)
                            ->setNodeEnv([
                                'HOME' => '/tmp',
                                'XDG_CACHE_HOME' => '/tmp',
                                'PUPPETEER_CACHE_DIR' => '/tmp/puppeteer',
                            ]);

                        if ($chromePath) {
                            $browsershot->setChromePath($chromePath);
                        }

                        $websiteItem['website_html'] = $browsershot->bodyHtml();
                    } catch (\Throwable $e) {
                        $websiteItem['website_error'] = $e->getMessage();
                    }

                    $websiteContentArr[] = $websiteItem;
                }
            }




            $input_data = [
                'pdf_content' => $pdfContentArr,
                'csv_content' => $csvContentArr,
                'website_urls' => $websiteContentArr,
                'pgsql_tables' => $pgsqlFinalArr,
            ];
            $input_metric_data = [
                'pgsql_tables' => $pgsqlFinalArr,
                'pdf_content' => $pdfContentArr,
                'website_urls' => $websiteContentArr,
            ];



            $qda = [
                'pdf_content' => $pdfContentArr,
                'website_urls' => $websiteContentArr,
                //'open_ended_responses' => $csvDTTableService->getRecordsFromOpenEndedColumns($project),
            ];
            // return $csvDTTableService->getRecordsFromOpenEndedColumns($project);
            $qdaJobs = $qdaService->createJobs($project, $csvDTTableService->getRecordsFromOpenEndedColumns($project), $report, $request->model_key, $request->user());
            //$derivedChunkJobs=$qdaService->createDerivedColumnJobs($project, $request->model_key, $request->user());
            // $jsonQd = json_encode($qda);
            // return $qdaJobs;
            //$jsonData = json_encode($input_data);
            $jsonMetricData = json_encode($input_metric_data);


            $prompt = $request->input('prompt');
            $analysisPlanString = $prompt;


            //check for csv existence
            //check for pdf existence
            //check for open ended responses for first and incremental analysis

            $decideAndDispatch = new DispatchJobsService();

            $truthValues = $decideAndDispatch->decideAndDispatch($input_metric_data, $qda, $qdaJobs);
            $chain = [];
            $qdaExists = $truthValues['pdfExists'] || $truthValues['websiteContentExists'];
            $onlyQdaExists = $qdaExists && !$truthValues['pgsqlTableExists'] && !$truthValues['openEndedFirstChunkExists'] && !$truthValues['openEndedIncrementalExists'];

            if ($truthValues['pgsqlTableExists']) {
                $batchJobs = [];
                if ($truthValues['pgsqlTableExists']) {
                    // $batchJobs[] = new ManualModeMetricsDiscoveryJ($request->user(), $analysisPlanString,  $jsonMetricData, $report, $project, $request->model_key, $qda);
                    $chain[] = new IdentifyMetricsAndDerivedTableColumns($request->user(), $analysisPlanString,  $jsonMetricData, $report, $project, $request->model_key);
                    $chain[] = new DispatchDerivedTableJobs((int) $project->id, $userId, $request->model_key, $report->id, $prompt, $qda);
                }

                // Only add the batch if we actually have jobs
                if (!empty($batchJobs)) {
                    // $chain[] = Bus::batch($derivedChunkJobs)->allowFailures();
                    // $chain[] = Bus::batch($batchJobsDerived)->allowFailures();
                }
            }
            if ($onlyQdaExists && !$truthValues['pgsqlTableExists']) {
                $chain[]= new CreateDashboardJ($request->user()->id, $prompt, $report->id, $project->id, $request->model_key, $qda);
                DB::table('report_logs')->where('report_id', '=', $report->id)->delete();
                event(new ReportStatusUpdate(reportId: $report->id));

                Bus::chain($chain)->dispatch();
            }
            if (!empty($chain) && !$onlyQdaExists) {
                DB::table('report_logs')->where('report_id', '=', $report->id)->delete();
                event(new ReportStatusUpdate(reportId: $report->id));
                Bus::chain($chain)->dispatch();
            }
            
            $idb = null;

            // if ($truthValues['pgsqlTableExists'] && $truthValues['pdfExists'] && $truthValues['websiteContentExists'] && $truthValues['openEndedFirstChunkExists'] && $truthValues['openEndedIncrementalExists']) {
            //     Bus::chain([

            //         Bus::batch([
            //             new ManualModeMetricsDiscoveryJ($request->user(), $analysisPlanString,  $jsonMetricData, $report, $project, $request->model_key),
            //             new ManualModeQualitativeDataInsightsJ($request->user(), $jsonQda, $report, $project, $request->model_key)
            //         ])->allowFailures(),
            //         Bus::batch($qdaJobs['first_chunk_jobs'] ?? [])->allowFailures(),
            //         Bus::batch($qdaJobs['remaining_chunk_jobs'] ?? [])->allowFailures(),
            //         new CreateDashboardJ($request->user(), $prompt, $report, $project, $request->model_key)

            //     ])->dispatch();
            // }

            return to_route('reports.edit', $report->id)
                ->with('message', 'Agents are analyzing the data. An email will be sent to you with the link to the dashboard. You may run multiple reports at once.');
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'report_id' => 'nullable|integer|exists:reports,id',
            'project_id' => 'required|exists:projects,id',
            'is_automatic' => 'required|boolean',
            'prompt' => 'required|string',
            'result' => 'required|string',
            'title' => 'required|string|max:255',
            'model_key' => 'required|string',
        ]);

        $userId = Auth::id();
        $isAutomatic = $request->boolean('is_automatic');

        if ($request->filled('report_id')) {
            $report = Report::where('id', $request->integer('report_id'))
                ->where('user_id', $userId)
                ->where('project_id', $request->project_id)
                ->first();

            if (!$report) {
                return response()->json(['message' => 'Invalid report_id.'], 403);
            }

            $report->update([
                'title' => $request->title,
                'prompt' => $request->prompt,
                'result' => $request->result,
                'is_automatic' => $isAutomatic,
                'model_key' => $request->model_key,
            ]);
        } else {
            Report::create([
                'user_id' => $userId,
                'uuid' => (string) Str::uuid(),
                'title' => $request->title,
                'project_id' => $request->project_id,
                'prompt' => $request->prompt,
                'result' => $request->result,
                'is_automatic' => $isAutomatic,
                'model_key' => $request->model_key,
            ]);
        }
        return to_route('projects.reports.index', $request->project_id);
    }

    public function arstore(Request $request)
    {
        //
        $request->validate([
            'report_id' => 'nullable|integer|exists:reports,id',
            'project_id' => 'required|exists:projects,id',
            'is_automatic' => 'required|boolean',
            'prompt' => 'required|string',
            'result' => 'required|string',
            'title' => 'required|string|max:255',
            'model_key' => 'required|string',
        ]);

        $userId = Auth::id();
        $isAutomatic = $request->boolean('is_automatic');

        if ($request->filled('report_id')) {
            $report = Report::where('id', $request->integer('report_id'))
                ->where('user_id', $userId)
                ->where('project_id', $request->project_id)
                ->first();

            if (!$report) {
                return response()->json(['message' => 'Invalid report_id.'], 403);
            }

            $report->update([
                'title' => $request->title,
                'prompt' => $request->prompt,
                'result' => $request->result,
                'is_automatic' => $isAutomatic,
                'model_key' => $request->model_key,
            ]);
        } else {
            Report::create([
                'user_id' => $userId,
                'uuid' => (string) Str::uuid(),
                'title' => $request->title,
                'project_id' => $request->project_id,
                'prompt' => $request->prompt,
                'result' => $request->result,
                'is_automatic' => $isAutomatic,
                'model_key' => $request->model_key,
            ]);
        }
        return to_route('projects.reports.index', $request->project_id);
    }
    /**
     * Display the specified resource.
     */
    public function show($uuid)
    {
        try {
            $report = Report::where('uuid', $uuid)->firstOrFail();
            $html = $this->renderReportHtml($report, false);

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load report: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
        try {
            $report->reportLogs;
            return Inertia::render('Reports/Edit', [
                'report' => $report,
                'project' => $report->project,
                'aiModels' => (new AIModel())->getModels(),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load report: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'prompt' => 'required|string',
                'result' => 'required|string',
                'model_key' => 'required|string',
            ]);

            $report->update([
                'title' => $request->title,
                'prompt' => $request->prompt,
                'result' => $request->result,
                'model_key' => $request->model_key,
            ]);

            return to_route('projects.reports.index', $report->project_id);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update report: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
        try {
            $projectId = $report->project_id;
            $report->delete();
            return to_route('projects.reports.index', $projectId);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to delete report: ' . $e->getMessage()])->withInput();
        }
    }
}
