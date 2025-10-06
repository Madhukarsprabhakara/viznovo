<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use Spatie\PdfToText\Pdf; 
use Illuminate\Support\Str;
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

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Project $project)
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

            $filePath = storage_path('app/private/projects/8/NrTHGxxxU7dV8nNucSwZMBBgjjXX6qM3xPYBtJKv.pdf');
            return $content = Str::of(Pdf::getText($filePath))
             ->split("/\f/")
            ->toArray();
            return $project;
        }
        catch (\Exception $e) {
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
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
