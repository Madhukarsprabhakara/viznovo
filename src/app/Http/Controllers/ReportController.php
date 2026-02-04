<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Str;
use App\Services\AIService;
use League\Csv\Reader;
use League\Csv\Statement;
class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        //
        try {

            return Inertia::render('Reports/Show', [
                'project' => $project,
                'reports' => $project->reports,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load project: ' . $e->getMessage()])->withInput();
        }
    }

    public function createForm(Project $project)
    {
        try {
            return Inertia::render('Reports/Create', [
                'project' => $project,
                'reports' => $project->reports,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load project: ' . $e->getMessage()])->withInput();
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function batch(Request $request, Project $project, AIService $aiService)
    {
        //
        try {
            //access the csv
            //get 500 rows at a time
            //create batch jsonl file
            //send to openai
            //store results
            //take individual results and consolidate into final report using openai
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function create(Request $request, Project $project, AIService $aiService)
    {
        //
        try {
            // sleep(10); // Simulate a delay for processing
            //get all pdf data for the project
            //extract text from the pdfs
            //create a json response from extracted text
            //send the json data along with the prompt to open ai
            //get the response from openai
            //store it in reports table
            // Get all PDF files for the project
            $allFiles = $project->files;
            // $allFiles = $project->files()->where('type', 'application/pdf')->get();

            $pdfContentArr = [];
            $csvContentArr = [];
            foreach ($allFiles as $file) {
                if ($file->type === 'application/pdf') {
                    // Adjust the disk and path as per your storage setup
                    $filePath = storage_path('app/private/' . $file->url);

                    $content = '';
                    try {
                        $content = Str::of(Pdf::getText($filePath))
                            ->split("/\f/")
                            ->toArray();
                    } catch (\Exception $e) {
                        $content = 'Could not extract text: ' . $e->getMessage();
                    }

                    $pdfContentArr[] = [
                        'filename' => $file->name ?? basename($file->system_name),
                        'pdf_content' => $content,
                    ];
                }
                if ($file->type === 'text/csv') {
                    $filePath = storage_path('app/private/' . $file->url);

                    try {
                        // Create reader and assume first row is header
                        $csv = Reader::createFromPath($filePath, 'r');
                        $csv->setHeaderOffset(0);
                        $csv->setEscape('');

                        $stmt = new Statement()
                            ->limit(1000);

                        $records = $stmt->process($csv);
                        // return response()->json($records);
                        // Convert records iterator to array of associative arrays
                        // return $records = iterator_to_array($csv->getRecords(), false);
                    } catch (\League\Csv\Exception $e) {
                        // Fallback: try a simple parse if the CSV has no header or parsing fails
                        try {
                            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            $records = array_map(function ($line) {
                                return str_getcsv($line);
                            }, $lines);
                        } catch (\Exception $e2) {
                            $records = ['error' => 'Could not parse CSV: ' . $e2->getMessage()];
                        }
                    } catch (\Exception $e) {
                        $records = ['error' => 'Could not read CSV: ' . $e->getMessage()];
                    }

                    $csvContentArr[] = [
                        'csv_filename' => $file->name ?? basename($file->system_name),
                        'csv_data' => $records,
                    ];
                }
            }




            $input_data = [
                'pdf_content' => $pdfContentArr,
                'csv_content' => $csvContentArr,
            ];

            $jsonData = json_encode($input_data);
            $result = $aiService->getOpenAIReport($request->prompt, $jsonData);
            // $result = $aiService->getGeminiAI($request->prompt, $jsonData);
            return $result;
            // return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'prompt' => 'required|string',
            'result' => 'required|string',
            'title' => 'required|string|max:255',
        ]);
        Report::create([
            'user_id' => auth()->id(),
            'uuid' => Str::uuid(),
            'title' => $request->title,
            'project_id' => $request->project_id,
            'prompt' => $request->prompt,
            'result' => $request->result,
        ]);

        return to_route('projects.reports.index', $request->project_id);
    }

    /**
     * Display the specified resource.
     */
    public function show($uuid)
    {
        //
        try {
            $report = Report::where('uuid', $uuid)->firstOrFail();
            $content = $report->result;
            return view('Global.public_report', compact('content'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load report: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
        try {
            return Inertia::render('Reports/Edit', [
                'report' => $report,
                'project' => $report->project,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load report: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
        try {
            $request->validate([
                'prompt' => 'required|string',
                'result' => 'required|string',
            ]);

            $report->update([
                'prompt' => $request->prompt,
                'result' => $request->result,
            ]);

            return to_route('projects.reports.index', $report->project_id);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update report: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
        try {
            $projectId = $report->project_id;
            $report->delete();
            return to_route('projects.reports.index', $projectId);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to delete report: ' . $e->getMessage()])->withInput();
        }
    }
}
