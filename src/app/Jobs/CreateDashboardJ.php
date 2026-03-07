<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Report;
use App\Models\ReportLog;
use App\Models\ReportLogOpenEnded;
use App\Services\ProjectDataMetricsService;
use App\Services\JsonDataService;
use App\Ai\Agents\CreateDashboard;

class CreateDashboardJ implements ShouldQueue
{
    use Queueable;

    protected $report;
    protected $prompt;
    protected $project;
    protected $modelKey;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $prompt, Report $report, $project, $modelKey)
    {
        $this->report = $report;
        $this->prompt = $prompt;
        $this->project = $project;
        $this->modelKey = $modelKey;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $jsonDataService = new JsonDataService();
        $projectDataMetricsService = new ProjectDataMetricsService();
        $pdf_website_content_insights = ReportLog::where('report_id', $this->report->id)
            ->where('agent', 'ManualModeQualitativeDataInsights')
            ->first()->response;
        $open_ended_response_insights = ReportLogOpenEnded::query()
            ->where('report_id', $this->report->id)
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

            'metrics_insights' => $projectDataMetricsService->getDataForPromptDesign($this->report->id),
            'qualitative_data_insights' => $qdaInsightsDecoded['qualitative_insights'] ?? null,
        ];

        if ($this->modelKey == 'gpt-5') {
            $response = (new CreateDashboard)->forUser($this->user)
                ->prompt(
                    'Here are the instructions...\n\n' . $this->prompt . ' and the insights:' . json_encode($data_for_prompt_design),
                    provider: [
                        'openai' => 'gpt-5.2',
                        'gemini' => 'gemini-3.1-pro-preview',
                    ],
                    timeout: 600,
                );
        } else {
            $response = (new CreateDashboard)->forUser($this->user)
                ->prompt(
                    'Here are the instructions...\n\n' . $this->prompt . ' and the insights:' . json_encode($data_for_prompt_design),
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
        $startEpoch = $this->report->start_epoch;

        $timeTakenSeconds = is_null($startEpoch)
            ? null
            : max(0, $endEpoch - (int) $startEpoch);

        $this->report->update([
            'result' => $promptResponse,
            'end_epoch' => $endEpoch,
            'time_taken_seconds' => $timeTakenSeconds,
        ]);
    }
}
