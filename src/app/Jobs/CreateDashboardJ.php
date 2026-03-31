<?php

namespace App\Jobs;

use App\Ai\Agents\CreateDashboard;
use App\Events\ReportStatusUpdate;
use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Report;
use App\Models\ReportLog;
use App\Models\ReportLogOpenEnded;
use App\Models\User;
use App\Services\ProjectDataMetricsService;
use App\Services\JsonDataService;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\DB;
use App\Services\ReportLogService;

#[Timeout(660)]
#[Tries(3)]

class CreateDashboardJ implements ShouldQueue
{
    use Queueable;

    protected ?int $userId;
    protected string $prompt;
    protected int $reportId;
    protected int $projectId;
    protected ?string $modelKey;
    protected mixed $qualitativeDataRaw;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $userId, string $prompt, int $reportId, int $projectId, ?string $modelKey, mixed $qualitativeDataRaw = null)
    {
        $this->userId = $userId;
        $this->prompt = $prompt;
        $this->reportId = $reportId;
        $this->projectId = $projectId;
        $this->modelKey = $modelKey;
        $this->qualitativeDataRaw = $qualitativeDataRaw;
    }

    /**
     * Execute the job.
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->reportId)->releaseAfter(30)->expireAfter(720)];
    }
    public function handle(): void
    {
        $reportLogService = new ReportLogService();
        $report = Report::find($this->reportId);
        $project = Project::find($this->projectId);
        $user = $this->userId !== null ? User::find($this->userId) : null;

        if (!$report || !$project) {
            return;
        }

        $jsonDataService = new JsonDataService();
        $projectDataMetricsService = new ProjectDataMetricsService();
        $pdf_website_content_insights = ReportLog::where('report_id', $report->id)
            ->where('agent', 'ManualModeQualitativeDataInsights')
            ->first()?->response;
        $open_ended_response_insights = ReportLogOpenEnded::query()
            ->where('report_id', $report->id)
            ->whereIn('agent', [
                'ManualModeQualitativeCsvDataInsights',
                'ManualModeQualitativeCsvDataInsightsIncrement',
            ])
            ->whereNotNull('response')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('response');

        $qdaInsightsDecoded = json_decode($pdf_website_content_insights, true);
        $qdaOpoenEndedInsights = json_decode($open_ended_response_insights, true);
        $qdaInsightsDecoded['qualitative_insights'][] = $qdaOpoenEndedInsights['qualitative_insights'] ?? null;
        $data_for_prompt_design = [
            // 'analysis_plan' => $analysisPlanArray['analysis_plan'] ?? null,
            'qualitative_data_raw' => $this->qualitativeDataRaw,
            'metrics_insights' => $projectDataMetricsService->getDataForPromptDesign($report->id),
            'qualitative_data_insights' => $qdaInsightsDecoded['qualitative_insights'] ?? null,
        ];

        if ($this->modelKey == 'gpt-5') {
            $reportLogService->storeReportLogs($this->reportId, 'CreateDashboard', 'Started creating report.');
            event(new ReportStatusUpdate(reportId: $this->reportId));
            $response = (new CreateDashboard)->forUser($user)
                ->prompt(
                    'Here are the instructions...\n\n' . $this->prompt . ' qualitative data and the insights:' . json_encode($data_for_prompt_design),
                    provider: [
                        'openai' => 'gpt-5.2',
                        'gemini' => 'gemini-3.1-pro-preview',
                    ],
                    timeout: 600,
                );
        } else {
            $reportLogService->storeReportLogs($this->reportId, 'CreateDashboard', 'Started creating report.');
            event(new ReportStatusUpdate(reportId: $this->reportId));
            $response = (new CreateDashboard)->forUser($user)
                ->prompt(
                    'Here are the instructions...\n\n' . $this->prompt . ' qualitative data and the insights:' . json_encode($data_for_prompt_design),
                    provider: [
                        'gemini' => 'gemini-3.1-pro-preview',
                        'openai' => 'gpt-5.2',

                    ],
                    timeout: 600,
                );
        }

        $rawResponseText = (string) $response;
        [$decoded, $decodeError] = $jsonDataService->decodeAiJson($rawResponseText);
        $promptResponse = $jsonDataService->extractPromptResponse($decoded, $rawResponseText);

        $endEpoch = now()->timestamp;
        $startEpoch = $report->start_epoch;

        $timeTakenSeconds = is_null($startEpoch)
            ? null
            : max(0, $endEpoch - (int) $startEpoch);

        $report->update([
            'result' => $promptResponse,
            'end_epoch' => $endEpoch,
            'time_taken_seconds' => $timeTakenSeconds,
        ]);

        if ($report->result) {
            // log the status
            $reportLogService->storeReportLogs($this->reportId, 'CreateDashboard', 'Report created successfully.');
            event(new ReportStatusUpdate(reportId: $this->reportId));
            
        } else {
            event(new ReportStatusUpdate(reportId: $this->reportId));
            $reportLogService->storeReportLogs($this->reportId, 'CreateDashboard', 'Failed to create report: ' . ($decodeError ?? 'Unknown error'));
        }
    }
}
