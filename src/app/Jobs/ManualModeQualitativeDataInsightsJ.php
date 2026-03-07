<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Bus\Batchable;
use App\Ai\Agents\ManualModeQualitativeDataInsights;
use App\Services\JsonDataService;
use App\Models\ReportLog;

class ManualModeQualitativeDataInsightsJ implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */

    protected $user;
    protected $jsonQda;
    protected $report;
    protected $project;
    protected $modelKey;

    public function __construct(User $user, $jsonQda, $report, $project, ?string $modelKey = null)
    {
        $this->user = $user;
        $this->jsonQda = $jsonQda;
        $this->report = $report;
        $this->project = $project;
        $this->modelKey = $modelKey;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $jsonDataService = new JsonDataService();
        if ($this->modelKey == 'gpt-5') {
            $qdaInsights = (new ManualModeQualitativeDataInsights)->forUser($this->user)
                ->prompt(
                    'Here is all of the qualitative data gathered so far...\n\n' .  $this->jsonQda,
                    provider: [
                        'openai' => 'gpt-5.2',
                        'gemini' => 'gemini-3.1-pro-preview',
                    ],
                    timeout: 600,
                );
        } else {
            $qdaInsights = (new ManualModeQualitativeDataInsights)->forUser($this->user)
                ->prompt(
                    'Here is all of the qualitative data gathered so far...\n\n' .  $this->jsonQda,
                    provider: [
                        'gemini' => 'gemini-3.1-pro-preview',
                        'openai' => 'gpt-5.2',
                    ],
                    timeout: 600,
                );

        }



        $qdaInsightsString = (string) $qdaInsights;
        [$qdaInsightsDecoded, $decodeError] = $jsonDataService->decodeAiJson($qdaInsightsString);

        if ($qdaInsightsDecoded) {
            // log the status

            \DB::table('report_logs')
                ->updateOrInsert(
                    ['report_id' => $this->report->id, 'agent' => 'ManualModeQualitativeDataInsights'],
                    ['response' => json_encode($qdaInsightsDecoded), 'error' => null, 'created_at' => now(), 'updated_at' => now()]
                );
        } else {
            \DB::table('report_logs')
                ->updateOrInsert(
                    ['report_id' => $this->report->id, 'agent' => 'ManualModeQualitativeDataInsights'],
                    ['response' => null, 'error' => 'No qualitative insights found for the report.', 'created_at' => now(), 'updated_at' => now()]
                );
        }
    }
}
