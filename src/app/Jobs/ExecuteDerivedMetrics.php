<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\ProjectDataMetricsService;
use App\Services\ReportService;
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
        $projectDataMetricsService = new ProjectDataMetricsService();
        $reportService = new ReportService();
        $metrics = $reportService->getMetricsForReport($reportService->getReportById($this->report_id));
        $projectDataMetricsService->updateMetricResult($metrics);

    }
}
