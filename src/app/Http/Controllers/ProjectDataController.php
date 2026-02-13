<?php

namespace App\Http\Controllers;

use App\Models\ProjectData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(ProjectData $projectData)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectData $projectData)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectData $projectData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectData $projectData)
    {
        //
        try {
            // Delete the file from storage if it exists
           
            if (
                $projectData->url &&
                $projectData->type !== 'website' &&
                !str_starts_with($projectData->url, 'http://') &&
                !str_starts_with($projectData->url, 'https://') &&
                Storage::exists($projectData->url)
            ) {
                Storage::delete($projectData->url);
            }
            $projectData->delete();
            return back(303);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
