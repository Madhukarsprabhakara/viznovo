<?php

namespace App\Jobs;

use RuntimeException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Ai\Agents\CompleteDataSetCreation;
use App\Services\DerivedTableService;
use App\Services\JsonDataService;
use App\Services\ProjectDataMetricsService;
use App\Services\ProjectDataService;
use App\Services\ReportLogService;
use App\Services\UserAiProviderConfigService;
use App\Events\ReportStatusUpdate;

class IdentifyMetricsAndDerivedTableColumns implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    protected $user;
    protected $analysisPlanString;
    protected $jsonMetricData;
    protected $report;
    protected $reportId;
    protected $project;
    protected $modelKey;

    public function __construct($user, $analysisPlanString, $jsonMetricData, $report, $project, $modelKey)
    {
        //
        $this->user = $user;
        $this->analysisPlanString = $analysisPlanString;
        $this->jsonMetricData = $jsonMetricData;
        $this->report = $report;
        $this->reportId = is_object($report) ? ($report->id ?? null) : $report;
        $this->project = $project;
        $this->modelKey = $modelKey;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //get the derived table columns and metrics
        app(UserAiProviderConfigService::class)->applyForUser($this->user?->id);

        $reportLogService = new ReportLogService();
        $jsonDataService = new JsonDataService();
        $projectDataMetricsService = new ProjectDataMetricsService();
        $projectDataService = new ProjectDataService();
        $derivedTableService = new DerivedTableService();
        $reportLogService->storeReportLogs($this->reportId, 'IdentifyMetricsAndDerivedTableColumns', 'Creating additional columns and tables as needed to analyze the data.');
        event(new ReportStatusUpdate(reportId: $this->reportId));

        if ($this->modelKey == 'gpt-5') { 
            $intermediate_tables = (new CompleteDataSetCreation)->forUser($this->user)
            ->prompt(
                'Here is the data analysis plan...\n\n' . $this->analysisPlanString . '\n\n Here is the sample data and the postgres table schema from the sources...' .  $this->jsonMetricData . '\n\n',
                provider: [
                    'openai' => 'gpt-5.4',
                    'gemini' => 'gemini-3.1-pro-preview',
                    'ollama' => 'gemma4:e4b',
                ],
                timeout: 600,
            );
        }
        if ($this->modelKey == 'gemini-3-pro') { 
            $intermediate_tables = (new CompleteDataSetCreation)->forUser($this->user)
            ->prompt(
                'Here is the data analysis plan...\n\n' . $this->analysisPlanString . '\n\n Here is the sample data and the postgres table schema from the sources...' .  $this->jsonMetricData . '\n\n',
                provider: [
                    'gemini' => 'gemini-3.1-pro-preview',
                    'openai' => 'gpt-5.4',
                    'ollama' => 'gemma4:e4b',
                ],
                timeout: 600,
            );
        }
        if ($this->modelKey == 'gemma4:e4b') { 
           $intermediate_tables = (new CompleteDataSetCreation)->forUser($this->user)
            ->prompt(
                'Here is the data analysis plan...\n\n' . $this->analysisPlanString . '\n\n Here is the sample data and the postgres table schema from the sources...' .  $this->jsonMetricData . '\n\n',
                provider: [
                    'ollama' => 'gemma4:e4b',
                    'gemini' => 'gemini-3.1-pro-preview',
                    'openai' => 'gpt-5.4',
                    
                ],
                timeout: 600,
            ); 
        }
        
        $rawResponseText = (string) $intermediate_tables;

        [$decoded, $decodeError] = $jsonDataService->decodeAiJson($rawResponseText);

        if (! is_array($decoded)) {
            throw new RuntimeException($decodeError ?: 'AI response could not be decoded into a metrics payload.');
        }

        $decoded['project_id'] = $this->project->id ?? ($decoded['project_id'] ?? null);
        $decoded['user_id'] = $this->user->id;

        $storedMetrics = $projectDataMetricsService->store($decoded, $this->user->id, $this->reportId);
        $storedDerivedColumns = $derivedTableService->storeDerivedColumns($decoded['intermediate_tables'] ?? [], $this->user->id, $this->reportId);
        $storedTableName = $projectDataService->storeDerivedTableName($decoded['intermediate_tables'] ?? []);
        // if ($projectDataMetricsService->checkMetricsExistForReport($this->report->id)) {
        //     // log the status
        //     event(new ReportStatusUpdate(reportId: $this->report->id));
        //     \DB::table('report_logs')
        //         ->updateOrInsert(
        //             ['report_id' => $this->report->id, 'agent' => 'ManualModeMetricsDiscovery'],
        //             ['response' => json_encode($metricSqls), 'error' => null, 'created_at' => now(), 'updated_at' => now(), 'display_message' => 'Metrics discovered successfully.']
        //         );

        // } else {
        //     event(new ReportStatusUpdate(reportId: $this->report->id));
        //     \DB::table('report_logs')
        //         ->updateOrInsert(
        //             ['report_id' => $this->report->id, 'agent' => 'ManualModeMetricsDiscovery'],
        //             ['response' => null, 'error' => 'No metrics found for the report.', 'created_at' => now(), 'updated_at' => now(), 'display_message' => 'Something went wrong with metrics discovery.']
        //         );
        // }
        //create project_data_csv entries for the derived tables with the same project_data_id as the original table and csv_text_table_name as the derived table name
        //dervied table should have all columns from original table and the calculated columns as well
    }
}
