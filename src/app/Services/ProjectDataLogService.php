<?php

namespace App\Services;
use App\Models\ProjectDataLog;

class ProjectDataLogService
{

    public function log(array $log)
    {
        $projectDataLogService= new ProjectDataLog($log);
        $projectDataLogService->save();
    }
    
    
}
