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
use App\Events\CsvStatusUpdate;
use App\Services\ProjectDataLogService;
use App\Models\ProjectDataLog;
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
        // event(new CsvStatusUpdate(status_message: 'CSV Text Table Created', project_data_id: $this->projectData->id));

        //log the creation of csv text table
        $projectDataLogService = new ProjectDataLogService();

        $projectDataLog= [
            'project_data_id' => $this->projectData->id,
            'status_message' => 'CSV Text Table Created',
            'job' => 'CreateCsvTextTable',
        ];
        $projectDataLogService->log($projectDataLog);
        $this->projectData->projectDataLogs;
        // event(new CsvStatusUpdate(projectData: $this->projectData, project_data_id: $this->projectData->id));
    }
}
