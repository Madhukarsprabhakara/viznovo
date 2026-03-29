<?php

namespace App\Jobs;

use App\Models\ProjectData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Services\DerivedTableService;
use App\Services\ProjectDataLogService;
use Illuminate\Support\Facades\Log;

class AddRecordsDerivedTable implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    protected string $schemaName;
    protected int $projectDataId;

    public function __construct(string $schemaName, int $projectDataId)
    {
        $this->schemaName = $schemaName;
        $this->projectDataId = $projectDataId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $projectData = ProjectData::find($this->projectDataId);

        if (!$projectData) {
            Log::warning('Skipping derived table population because the project data row no longer exists.', [
                'project_data_id' => $this->projectDataId,
                'schema_name' => $this->schemaName,
            ]);

            return;
        }

        $derivedTableService = new DerivedTableService();
        $tableName = (string) $projectData->csv_derived_table_name;
        $records = $derivedTableService->getSourceRecordsForDerivedTable($projectData, $this->schemaName);
        $inserted = $derivedTableService->addRecordsDerivedTable($this->schemaName, $tableName, $records);

        $projectData->is_csv_derived_table_populated = true;
        $projectData->save();

        $projectDataLogService = new ProjectDataLogService();
        $projectDataLogService->log([
            'project_data_id' => $projectData->id,
            'status_message' => 'Derived table populated with ' . $inserted . ' records',
            'job' => 'AddRecordsDerivedTable',
        ]);
    }
}
