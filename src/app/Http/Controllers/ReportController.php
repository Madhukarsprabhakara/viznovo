<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Str;
use App\Services\AIService;
use App\Models\AIModel;
use League\Csv\Reader;
use League\Csv\Statement;
use App\Ai\Agents\DiscoverFiles;
use App\Ai\Agents\CreatePrompt5dImpact;
use App\Ai\Agents\CustomResearch;
use GuzzleHttp\Promise\Create;
use Spatie\Browsershot\Browsershot;

use Illuminate\Support\Facades\Auth;

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
        if (stripos($trimmed, '<html') !== false || stripos($trimmed, '<!doctype html') !== false) {
            return $trimmed;
        }

        return null;
    }

    private function decodeAiJson(string $rawText): array
    {
        $trimmed = trim($rawText);

        $candidates = [];
        $candidates[] = $trimmed;

        // Strip common code fences.
        $noFences = preg_replace('/^\s*```(?:json)?\s*/i', '', $trimmed);
        $noFences = preg_replace('/\s*```\s*$/', '', (string) $noFences);
        $noFences = trim((string) $noFences);
        if ($noFences !== '' && $noFences !== $trimmed) {
            $candidates[] = $noFences;
        }

        // Sometimes the whole payload is wrapped in quotes.
        foreach ([$trimmed, $noFences] as $v) {
            $v = trim((string) $v);
            if (strlen($v) >= 2 && ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'")))) {
                $candidates[] = trim(substr($v, 1, -1));
            }
        }

        // Attempt to repair the common invalid pattern: [{""prompt_response"":""...""}]
        foreach ($candidates as $candidate) {
            if (str_contains($candidate, '""')) {
                $candidates[] = str_replace('""', '"', $candidate);
            }
        }

        // Try decoding each candidate.
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }

            $decoded = json_decode($candidate, true);
            $error = json_last_error();
            $errorMessage = json_last_error_msg();

            // Some providers / gateways may return JSON as a quoted string (double-encoded).
            if (is_string($decoded)) {
                $decoded2 = json_decode($decoded, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [$decoded2, null];
                }
            }

            if ($error === JSON_ERROR_NONE) {
                return [$decoded, null];
            }
        }

        // Last error message based on raw trimmed input.
        json_decode($trimmed, true);
        return [null, json_last_error_msg() ?: 'Invalid JSON'];
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
    public function autoCreate(Request $request, Project $project, AIService $aiService)
    {
        //
        try {
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
            $websiteContentArr = [];
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
                    $filePath = storage_path('app/private/' . $file->url);

                    try {
                        // Create reader and assume first row is header
                        $csv = Reader::createFromPath($filePath, 'r');
                        $csv->setHeaderOffset(0);
                        $csv->setEscape('');

                        $stmt = new Statement()
                            ->limit(1000);

                        $records = $stmt->process($csv);
                        // return response()->json($records);
                        // Convert records iterator to array of associative arrays
                        // return $records = iterator_to_array($csv->getRecords(), false);
                    } catch (\League\Csv\Exception $e) {
                        // Fallback: try a simple parse if the CSV has no header or parsing fails
                        try {
                            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            $records = array_map(function ($line) {
                                return str_getcsv($line);
                            }, $lines);
                        } catch (\Exception $e2) {
                            $records = ['error' => 'Could not parse CSV: ' . $e2->getMessage()];
                        }
                    } catch (\Exception $e) {
                        $records = ['error' => 'Could not read CSV: ' . $e->getMessage()];
                    }

                    $csvContentArr[] = [
                        'csv_filename' => $file->name ?? basename($file->system_name),
                        'csv_data' => $records,
                    ];
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
            ];


            $jsonData = json_encode($input_data);
            // File discovery
            // create dashboard based on the insights from file discovery agent


            if ($request->model_key == 'gpt-5') {
                $discovery = (new DiscoverFiles)->forUser($request->user())
                    ->prompt(
                        'Here are the files and its contents...\n\n' . $jsonData,
                        provider: [
                            'openai' => 'gpt-5.2',
                            'gemini' => 'gemini-3-pro-preview',
                        ],
                        timeout: 600,
                    );

                $discovery_string = (string) $discovery;
                sleep(60);
                $prompt_dd = (new CreatePrompt5dImpact)->forUser($request->user())
                    ->prompt(
                        'Here are the summary of the file and url contents...\n\n' . $discovery_string,
                        provider: [
                            'openai' => 'gpt-5.2',
                            'gemini' => 'gemini-3-pro-preview',
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
                            'openai' => 'gpt-5.2',
                            'gemini' => 'gemini-3-pro-preview',
                        ],
                        timeout: 600,
                    );
            }
            if ($request->model_key == 'gemini-3-pro') {
                $discovery = (new DiscoverFiles)->forUser($request->user())
                    ->prompt(
                        'Here are the files and its contents...\n\n' . $jsonData,
                        provider: [
                            'gemini' => 'gemini-3-pro-preview',
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
                            'gemini' => 'gemini-3-pro-preview',
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
                            'gemini' => 'gemini-3-pro-preview',
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

            return response()->json([
                'next_agent_prompt' => $nextAgentPrompt,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function create(Request $request, Project $project, AIService $aiService)
    {
        //
        try {
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
            $result = null;
            $pdfContentArr = [];
            $csvContentArr = [];
            $websiteContentArr = [];
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
                    $filePath = storage_path('app/private/' . $file->url);

                    try {
                        // Create reader and assume first row is header
                        $csv = Reader::createFromPath($filePath, 'r');
                        $csv->setHeaderOffset(0);
                        $csv->setEscape('');

                        $stmt = new Statement()
                            ->limit(1000);

                        $records = $stmt->process($csv);
                        // return response()->json($records);
                        // Convert records iterator to array of associative arrays
                        // return $records = iterator_to_array($csv->getRecords(), false);
                    } catch (\League\Csv\Exception $e) {
                        // Fallback: try a simple parse if the CSV has no header or parsing fails
                        try {
                            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            $records = array_map(function ($line) {
                                return str_getcsv($line);
                            }, $lines);
                        } catch (\Exception $e2) {
                            $records = ['error' => 'Could not parse CSV: ' . $e2->getMessage()];
                        }
                    } catch (\Exception $e) {
                        $records = ['error' => 'Could not read CSV: ' . $e->getMessage()];
                    }

                    $csvContentArr[] = [
                        'csv_filename' => $file->name ?? basename($file->system_name),
                        'csv_data' => $records,
                    ];
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
            ];


            // return response()->json($input_data);
            $jsonData = json_encode($input_data);


            $prompt = $request->input('prompt');

            if ($request->model_key == 'gpt-5') {
                $response = (new CustomResearch)->forUser($request->user())
                    ->prompt(
                        'Here are the instructions...\n\n' . $prompt . ' and the data:' . $jsonData,
                        provider: 'openai',
                        model: 'gpt-5.2',
                        timeout: 600,
                    );
            }
            if ($request->model_key == 'gemini-3-pro') {
                $response = (new CustomResearch)->forUser($request->user())
                    ->prompt(
                        'Here are the instructions...\n\n' . $prompt . ' and the data:' . $jsonData,
                        provider: 'gemini',
                        model: 'gemini-3-pro-preview',
                        timeout: 600,
                    );
            }

            $rawResponseText = (string) $response;
            [$decoded, $decodeError] = $this->decodeAiJson($rawResponseText);
            $promptResponse = $this->extractPromptResponse($decoded, $rawResponseText);

            if ($promptResponse === null) {
                return response()->json([
                    'message' => 'Response generated but could not be parsed (invalid JSON from model).',
                    'decode_error' => $decodeError,
                    'raw_response_preview' => Str::limit((string) $rawResponseText, 4000),
                ], 422);
            }

            return [
                'status' => 'success',
                'message' => 'Response generated successfully.',
                'data' => $promptResponse,
            ];


            // 

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
            'project_id' => 'required|exists:projects,id',
            'is_automatic' => 'required|boolean',
            'prompt' => 'required|string',
            'result' => 'required|string',
            'title' => 'required|string|max:255',
            'model_key' => 'required|string',
        ]);
        Report::create([
            'user_id' => Auth::id(),
            'uuid' => Str::uuid(),
            'title' => $request->title,
            'project_id' => $request->project_id,
            'prompt' => $request->prompt,
            'result' => $request->result,
            'is_automatic' => $request->boolean('is_automatic'),
            'model_key' => $request->model_key,

        ]);
        return to_route('projects.reports.index', $request->project_id);
    }

    public function arstore(Request $request)
    {
        //
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'is_automatic' => 'required|boolean',
            'prompt' => 'required|string',
            'result' => 'required|string',
            'title' => 'required|string|max:255',
            'model_key' => 'required|string',
        ]);
        Report::create([
            'user_id' => Auth::id(),
            'uuid' => Str::uuid(),
            'title' => $request->title,
            'project_id' => $request->project_id,
            'prompt' => $request->prompt,
            'result' => $request->result,
            'is_automatic' => $request->boolean('is_automatic'),
            'model_key' => $request->model_key,

        ]);
        return to_route('projects.reports.index', $request->project_id);
    }
    /**
     * Display the specified resource.
     */
    public function show($uuid)
    {
        //
        try {
            $report = Report::where('uuid', $uuid)->firstOrFail();
            $content = $report->result;
            return view('Global.public_report', compact('content'));
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
                'prompt' => 'required|string',
                'result' => 'required|string',
                'model_key' => 'required|string',
            ]);

            $report->update([
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
