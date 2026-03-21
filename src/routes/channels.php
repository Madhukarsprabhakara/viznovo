<?php

use App\Models\ProjectData;
use App\Models\Report;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('csv.status.update.{projectDataId}', function ($user, int $projectDataId) {
    $projectData = ProjectData::find($projectDataId);
    if ((int) $projectData->user_id === (int) $user->id) {
        return true;
    }

    return $projectData->project && (int) $projectData->project->user_id === (int) $user->id;
});

Broadcast::channel('App.Models.Report.{reportId}', function ($user, int $reportId) {
    $report = Report::find($reportId);
    return (int) $report->user_id === (int) $user->id;
});
