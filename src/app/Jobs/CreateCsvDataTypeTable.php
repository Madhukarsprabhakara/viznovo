<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Services\ProjectService;
use App\Services\CsvDTTableService;
use App\Models\Project;
class CreateCsvDataTypeTable implements ShouldQueue
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
        $projectService = new ProjectService();
        $csvDTTableService = new CsvDTTableService();
        //get the schema and the table name
        //get table name from csv name
        $tableName = $this->projectData->csv_data_type_table_name;
        //get schema name from project table
        $schemaName = $projectService->getProjectSchema(Project::find($this->projectData->project_id));
        //get db columns and types from project_data_csvs table for the project data
        $columns = $csvDTTableService->getCsvDataTypeTableColumns($this->projectData); 
        //create a new table with the name csv_data_type_table_name in project data
        $csvDTTableService->createCsvDataTypeTable($tableName, $schemaName, $columns);
    }
}
