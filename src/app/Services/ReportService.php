<?php

namespace App\Services;
use App\Models\Project;
use App\Models\Report;
class ReportService
{

    public function listReports(Project $project)
    {
        return $project->reports;
    }
    public function getMetricsForReport(Report $report)
    {
        return $report->metrics;
    }
    public function getReportById(int $report_id): ?Report
    {
        return Report::find($report_id);
    }
    
    
}
