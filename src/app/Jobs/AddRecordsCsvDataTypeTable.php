<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Services\CsvDTTableService;
use App\Services\ProjectService;
use App\Models\Project;
class AddRecordsCsvDataTypeTable implements ShouldQueue
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
        $csvDTTableService = new CsvDTTableService();
        $records = [];
        $projectService = new ProjectService();
        $schemaName = $projectService->getProjectSchema(Project::find($this->projectData->project_id));
        $tableName = $this->projectData->csv_data_type_table_name;
        //get schema name and table name
        // $records = $csvDTTableService->getRecordsFromCsv($this->projectData->url, $schemaName, $tableName);
        //get the records
        $records=$csvDTTableService->getRecords($schemaName, $this->projectData->csv_text_table_name);
        // dd($records);
        //map db_column to csv_header from project_data_csvs table for the project data
        // $records = []; // get the records from the csv file using the url in project data and map the columns to the db columns using the project_data_csvs table
        $csvDTTableService->addRecordsToCsvDataTypeTable($schemaName, $tableName, $records);
        $this->projectData->is_csv_data_type_table_populated = true;
        $this->projectData->save();
    }
}
