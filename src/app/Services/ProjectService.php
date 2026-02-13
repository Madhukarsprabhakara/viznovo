<?php

namespace App\Services;
use App\Models\Project;
use App\Models\ProjectData;
class ProjectService
{

    public function storeProject($data)
    {
        // Logic to create a project
        $project = new \App\Models\Project();
        $project->user_id = auth()->id();
        $project->name = $data['name'];
        $project->description = $data['description'] ?? null;
        $project->is_archived = false;
        $project->save();

        return $project;
    }
    public function updateProject($project, $data)
    {
        // Logic to update a project
        $project->name = $data['name'];
        $project->description = $data['description'] ?? null;
        $project->save();

        return $project;
    }
    public function getProjectsByUser($userId)
    {
        return \App\Models\Project::where('user_id', $userId)->get();
    }
    public function handleFileUpload(Project $project, $file)
    {
        // 1. Store the file in a project-specific directory
        $path = $file->store("projects/{$project->id}");

        // 2. Optionally, save file info to the database
        // (Assuming you have a ProjectFile model and migration)
        $projectData = new ProjectData();
        $projectData->user_id = auth()->id();
        $projectData->project_id = $project->id;
        $projectData->name = $file->getClientOriginalName();
        $projectData->system_name = basename($path);
        $projectData->type = $file->getClientMimeType();
        $projectData->url = $path;
        $projectData->save();

        // 3. Return the path or any other info if needed
        return $projectData;
    }

    public function handleUrlSource(Project $project, string $url)
    {
        $normalizedUrl = trim($url);

        $projectData = new ProjectData();
        $projectData->user_id = auth()->id();
        $projectData->project_id = $project->id;
        $projectData->name = $normalizedUrl;
        $projectData->system_name = 'url_' . substr(sha1($normalizedUrl), 0, 16);
        $projectData->type = 'website';
        $projectData->url = $normalizedUrl;
        $projectData->save();

        return $projectData;
    }
}
