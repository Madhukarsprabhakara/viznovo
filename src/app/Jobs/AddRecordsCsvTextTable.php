<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Services\CsvTextTableService;
use App\Services\ProjectService;
use App\Models\Project;
use App\Events\CsvStatusUpdate;
use App\Services\ProjectDataLogService;
class AddRecordsCsvTextTable implements ShouldQueue
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
        $csvTextTableService = new CsvTextTableService();
        $records = [];
        $projectService = new ProjectService();
        $schemaName = $projectService->getProjectSchema(Project::find($this->projectData->project_id));
        $tableName = $this->projectData->csv_text_table_name;
        //get schema name and table name
        // $records = $csvTextTableService->getRecordsFromCsv($this->projectData->url, $schemaName, $tableName);
        //get the records
        $records=$csvTextTableService->getRecords($this->projectData);
        // dd($records);
        //map db_column to csv_header from project_data_csvs table for the project data
        // $records = []; // get the records from the csv file using the url in project data and map the columns to the db columns using the project_data_csvs table
        $csvTextTableService->addRecordsToCsvTextTable($schemaName, $tableName, $records);
        $this->projectData->is_csv_text_table_populated = true;
        $this->projectData->save();

        //log the creation of csv text table
        $projectDataLogService = new ProjectDataLogService();

        $projectDataLog= [
            'project_data_id' => $this->projectData->id,
            'status_message' => 'CSV Text Table Populated',
            'job' => 'AddRecordsCsvTextTable',
        ];
        $projectDataLogService->log($projectDataLog);
        $this->projectData->projectDataLogs;
        // event(new CsvStatusUpdate(projectData: $this->projectData, project_data_id: $this->projectData->id));
    }
}
