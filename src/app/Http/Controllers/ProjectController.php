<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ProjectService;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\CreateCsvTextTable;
use App\Jobs\AddRecordsCsvTextTable;
use App\Jobs\CreateCsvDataTypeTable;
use App\Jobs\IdentifyCsvColumnDataTypes;
use App\Jobs\AddRecordsCsvDataTypeTable;
use App\Services\CsvFileService;
use App\Events\CsvStatusUpdate;
use App\Services\ProjectDataLogService;
use App\Rules\ValidCsvHeaders;
use Illuminate\Validation\ValidationException;
use Throwable;
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

    public function getProjectEventData(Project $project)
    {
        //
        try {
            return  [
                'project' => $project,
                'files' => $project->files, // assuming $project->files returns the list
            ];
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
        try {
            return Inertia::render('Projects/Partials/Edit', [
                'project' => $project,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load project for editing: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, ProjectService $projectService)
    {
        //
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
            $projectService->updateProject($project, $request->all());
            return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
        try {

            $project->delete();
            return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back();
        }
    }

    public function upload(Request $request, Project $project, ProjectService $projectService, CsvFileService $csvFileService)
    {

        try {
            $request->validate([
                'files' => ['required', 'array'],
                'files.*' => [
                    'file',
                    'max:204800', // max 200MB per file
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (!$value instanceof \Illuminate\Http\UploadedFile) {
                            return;
                        }

                        $extension = strtolower(trim((string) $value->getClientOriginalExtension()));
                        if (!in_array($extension, ['csv', 'pdf', 'txt'], true)) {
                            $fail("The {$attribute} field must be a file of type: csv, pdf, txt.");
                        }
                    },
                    new ValidCsvHeaders(),
                ],
            ]);
            $user_id = $request->user()->id;
            $files = $request->file('files');
            foreach ($files as $file) {
                $projectData = $projectService->handleFileUpload($project, $file);

                if (strtolower($file->getClientOriginalExtension()) === 'csv') {

                    $projectData->csv_text_table_name = $csvFileService->getTextTableNameFromCsvName($file, $projectData->id);
                    $projectData->csv_data_type_table_name = $csvFileService->getDataTypeTableNameFromCsvName($file, $projectData->id);

                    $projectData->save();
                    // return $projectData;
                    // return $projectData->with(['projectDataLogs']);
                    //log the creation of csv data type table
                    $projectDataLogService = new ProjectDataLogService();

                    $projectDataLog = [
                        'project_data_id' => $projectData->id,
                        'status_message' => 'Starting import',
                        'job' => 'AddRecordsCsvDataTypeTable',
                    ];
                    $projectDataLogService->log($projectDataLog);
                    // return [
                    //     'project' => $project,
                    //     'files' => $project->files, // assuming $project->files returns the list
                    // ];
                    event(new CsvStatusUpdate(project_id: $project->id, project_data_id: $projectData->id, user_id: $user_id));

                    // [
                    //     'project' => $project,
                    //     'files' => $project->files, // assuming $project->files returns the list
                    // ]

                    Bus::batch([
                        [
                            new CreateCsvTextTable($projectData),
                            new AddRecordsCsvTextTable($projectData),
                            new IdentifyCsvColumnDataTypes($projectData),
                            new CreateCsvDataTypeTable($projectData),
                            new AddRecordsCsvDataTypeTable($projectData),
                        ],


                    ])->then(function (Batch $batch) use ($project, $projectData, $user_id) {
                        // All jobs completed successfully...log the success in a separate table with batch id and project data id
                        $projectData->status = 'Imported';
                        $projectData->save();
                        event(new CsvStatusUpdate(project_id: $project->id, project_data_id: $projectData->id, user_id: $user_id));
                    })->catch(function (Batch $batch, Throwable $e) {
                        // Batch job failure detected...
                    })->dispatch();
                }

                //dispatch a job to process csv file

            }

            // $projectService->handleFileUpload($project, $file);

            return redirect()->route('projects.show', $project)->with('success', 'File uploaded successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to upload file: ' . $e->getMessage()])->withInput();
        }
    }

    public function addUrl(Request $request, Project $project, ProjectService $projectService)
    {
        try {
            $validated = $request->validate([
                'url' => ['required', 'string', 'max:2048', 'url'],
            ]);

            $projectService->handleUrlSource($project, $validated['url']);

            return redirect()->route('projects.show', $project)->with('success', 'URL added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['url' => 'Failed to add URL: ' . $e->getMessage()])->withInput();
        }
    }
}
