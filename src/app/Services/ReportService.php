<?php

namespace App\Services;
use App\Models\Project;
use App\Models\ProjectData;
use App\Models\Report;
class ReportService
{

    public function listReports(Project $project)
    {
        return $project->reports;
    }
    
    
}
