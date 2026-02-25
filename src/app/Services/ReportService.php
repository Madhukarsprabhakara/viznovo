<?php

namespace App\Services;
use App\Models\Project;

class ReportService
{

    public function listReports(Project $project)
    {
        return $project->reports;
    }
    
    
}
