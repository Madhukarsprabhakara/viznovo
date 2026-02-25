<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Services\CsvTextTableService;
use App\Services\CsvFileService;
use App\Services\ProjectService;
use App\Services\ProjectDataCsvService;
use App\Models\Project;
class CreateCsvTextTable implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    protected $projectData;

    public function __construct($projectData)
    {
        $this->projectData = $projectData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $textTableService = new CsvTextTableService();
        $csvFileService = new CsvFileService();
        $projectService = new ProjectService();
        //get table name from csv name
        $tableName = $this->projectData->csv_text_table_name;
        //get schema name from project table
        $schemaName = $projectService->getProjectSchema(Project::find($this->projectData->project_id));
        //get table columns from csv name
        $columns = $csvFileService->getCsvTextTableColumns($this->projectData->url); 
        // dd($tableName, $schemaName, $columns);  
        //create table with the name and columns
        $textTableService->createCsvTextTable($tableName, $schemaName, $columns);

        //update project data and set is_csv_text_table_created to true
        $this->projectData->is_csv_text_table_created = true;
        $this->projectData->save();

        //add entries in project_data_csvs table for each column of csv
        $projectDataCsvService = new ProjectDataCsvService();
        $projectDataCsvService->storeCsvColumns($this->projectData, $columns, 'text_table', (int) $this->projectData->user_id);

    }
}
