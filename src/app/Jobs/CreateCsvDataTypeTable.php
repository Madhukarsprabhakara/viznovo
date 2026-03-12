<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Services\ProjectService;
use App\Services\CsvDTTableService;
use App\Models\Project;
use App\Events\CsvStatusUpdate;
use App\Services\ProjectDataLogService;
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

        //update project data and set is_csv_data_type_table_created to true
        $this->projectData->is_csv_data_type_table_created = true;
        $this->projectData->save();

        //log the creation of csv data type table
        $projectDataLogService = new ProjectDataLogService();

        $projectDataLog= [
            'project_data_id' => $this->projectData->id,
            'status_message' => 'CSV data type table created',
            'job' => 'CreateCsvDataTypeTable',
        ];
        $projectDataLogService->log($projectDataLog);
        $this->projectData->projectDataLogs;
        // event(new CsvStatusUpdate(projectData: $this->projectData, project_data_id: $this->projectData->id));
        // event(new CsvStatusUpdate(status_message: 'Records added to the table', project_data_id: $this->projectData->id));
    }
}
