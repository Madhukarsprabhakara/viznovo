<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ProjectService;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProjectService $projectService)
    {
        //
        return Inertia::render('Projects/Projects', [
            'projects' => $projectService->getProjectsByUser(auth()->id()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return Inertia::render('Projects/Partials/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProjectService $projectService)
    {
        //
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
            $project = $projectService->storeProject($request->all());
            return redirect()->route('projects.index')->with('success', 'Project created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create project: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        //
        try {
            return Inertia::render('Projects/Show', [
                'project' => $project,
                'files' => $project->files, // assuming $project->files returns the list
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load project: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }

    public function upload(Request $request, Project $project, ProjectService $projectService)
    {

        try {
            $request->validate([
                'files' => 'required|array',
                'files.*' => 'file|mimes:csv,pdf,txt|max:204800', // max 200MB per file
            ]);

            $files = $request->file('files');
            foreach ($files as $file) {
                $projectService->handleFileUpload($project, $file);
            }
            
            // $projectService->handleFileUpload($project, $file);

            return redirect()->route('projects.show', $project)->with('success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to upload file: ' . $e->getMessage()])->withInput();
        }
    }
}
