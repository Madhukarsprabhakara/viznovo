<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\ProjectDataMetricsService;
use App\Services\ReportService;
use App\Services\ReportLogService;
use App\Events\ReportStatusUpdate;
class ExecuteDerivedMetrics implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected int $report_id;
    public function __construct(int $report_id)
    {
        $this->report_id = $report_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $reportLogService = new ReportLogService();
        $projectDataMetricsService = new ProjectDataMetricsService();
        $reportService = new ReportService();
        $metrics = $reportService->getMetricsForReport($reportService->getReportById($this->report_id));
        $projectDataMetricsService->updateMetricResult($metrics);
        $reportLogService->storeReportLogs($this->report_id, 'ExecuteDerivedMetrics', 'Calculating metrics needed for analysis.');
        event(new ReportStatusUpdate(reportId: $this->report_id));

    }
}
