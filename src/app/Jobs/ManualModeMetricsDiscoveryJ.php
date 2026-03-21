<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Bus\Batchable;
use App\Services\JsonDataService;
use App\Services\ProjectDataMetricsService;
use App\Ai\Agents\ManualModeMetricsDiscovery;
use App\Events\ReportStatusUpdate;


class ManualModeMetricsDiscoveryJ implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */

    protected $user;
    protected $analysisPlanString;
    protected $jsonMetricData;
    protected $report;
    protected $project;
    protected $modelKey;

    public function __construct(User $user, $analysisPlanString, $jsonMetricData, $report, $project, ?string $modelKey = null)
    {
        $this->user = $user;
        $this->analysisPlanString = $analysisPlanString;
        $this->jsonMetricData = $jsonMetricData;
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

        //$tableDataString = json_encode($input_data['pgsql_tables']);
        $jsonDataService = new JsonDataService();
        $projectDataMetricsService = new ProjectDataMetricsService();
        if ($this->modelKey == 'gpt-5') {
            $metrics_sql = (new ManualModeMetricsDiscovery)->forUser($this->user)
                ->prompt(
                    'Here is the data analysis plan...\n\n' . $this->analysisPlanString . '\n\n Here is the sample data and the postgres table schema from the sources...' .  $this->jsonMetricData,
                    provider: [
                        'openai' => 'gpt-5.2',
                        'gemini' => 'gemini-3.1-pro-preview',
                    ],
                    timeout: 600,
                );
        } else {
            $metrics_sql = (new ManualModeMetricsDiscovery)->forUser($this->user)
                ->prompt(
                    'Here is the data analysis plan...\n\n' . $this->analysisPlanString . '\n\n Here is the sample data and the postgres table schema from the sources...' .  $this->jsonMetricData,
                    provider: [
                        'gemini' => 'gemini-3.1-pro-preview',
                        'openai' => 'gpt-5.2',
                    ],
                    timeout: 600,
                );
        }


        $metrics_sql_string = (string) $metrics_sql;
        [$promptDecoded, $promptDecodeError] = $jsonDataService->decodeAiJson($metrics_sql_string);
        $promptDecoded['project_id'] = $this->project->id;
        //$promptDecoded['project_data_id'] = $tableData['project_data_id'] ?? null;
        $promptDecoded['user_id'] = $this->user->id;
        $metricSqls[] = $promptDecoded ?? null;
        // You can further process each table's data here if needed
        // For example, you might want to summarize the schema or sample records
        $sqls = $projectDataMetricsService->store($metricSqls, $this->user->id, $this->report->id);
        if ($projectDataMetricsService->checkMetricsExistForReport($this->report->id)) {
            // log the status
            event(new ReportStatusUpdate(reportId: $this->report->id));
            \DB::table('report_logs')
                ->updateOrInsert(
                    ['report_id' => $this->report->id, 'agent' => 'ManualModeMetricsDiscovery'],
                    ['response' => json_encode($metricSqls), 'error' => null, 'created_at' => now(), 'updated_at' => now(), 'display_message' => 'Metrics discovered successfully.']
                );
            
        } else {
            event(new ReportStatusUpdate(reportId: $this->report->id));
            \DB::table('report_logs')
                ->updateOrInsert(
                    ['report_id' => $this->report->id, 'agent' => 'ManualModeMetricsDiscovery'],
                    ['response' => null, 'error' => 'No metrics found for the report.', 'created_at' => now(), 'updated_at' => now(), 'display_message' => 'Something went wrong with metrics discovery.']
                );
        }
    }
}
