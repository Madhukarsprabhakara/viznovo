<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Services\CsvTextTableService;
use App\Services\ProjectService;
use App\Services\JsonDataService;
use App\Services\ProjectDataCsvService;
use App\Models\Project;
use App\Models\User;
use App\Ai\Agents\DiscoverCSVColumnDataType;

class IdentifyCsvColumnDataTypes implements ShouldQueue
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
        $jsonDataService = new JsonDataService();
        $csvTextTableService = new CsvTextTableService();
        $projectService = new ProjectService();
        $schemaName = $projectService->getProjectSchema(Project::find($this->projectData->project_id));
        $tableName = $this->projectData->csv_text_table_name;

        //get 20 records from csv table
        $records = $csvTextTableService->getRecordsForDataTypeIdentification($this->projectData, $schemaName, $tableName, 20);
        $recordsString = json_encode($records); // convert the records to json format to send to openai or gemini
        //send 20 records to openai or gemini for data type identification
        $discovery = (new DiscoverCSVColumnDataType)->forUser(User::find($this->projectData->user_id))
            ->prompt(
                'Here are sample 20 records from the CSV table:\n\n' . $recordsString,
                provider: [
                    'openai' => 'gpt-5.2',
                    'gemini' => 'gemini-3-pro-preview',
                ],
                timeout: 600,
            );
       
        $rawPromptDd = (string) $discovery;
        
        [$promptDecoded, $promptDecodeError] = $jsonDataService->decodeAiJson($rawPromptDd);
        $columnDataTypes = is_array($promptDecoded) ? ($promptDecoded['column_data_types'] ?? null) : null;
        
        
        //save the openai/gemini response in an intermediate table
        $this->projectData->json_from_ai = json_encode($rawPromptDd);
        $this->projectData->json_from_ai_string = json_encode($columnDataTypes);
        $this->projectData->save();
        //Process the intermediate table and save the final data types in csv_data_types table
        //add entries in project_data_csvs table for each column of csv
        $projectDataCsvService = new ProjectDataCsvService();
        $projectDataCsvService->storeCsvColumns($this->projectData, $columnDataTypes, 'dt_table', (int) $this->projectData->user_id);


    }
}
