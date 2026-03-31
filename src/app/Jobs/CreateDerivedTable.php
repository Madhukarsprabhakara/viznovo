<?php

namespace App\Jobs;

use App\Models\ProjectData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Services\DerivedTableService;
use App\Services\ProjectDataLogService;
use Illuminate\Support\Facades\Log;

class CreateDerivedTable implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    protected int $projectDataId;
    protected string $schemaName;

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
            Log::warning('Skipping derived table creation because the project data row no longer exists.', [
                'project_data_id' => $this->projectDataId,
                'schema_name' => $this->schemaName,
            ]);

            return;
        }

        $derivedTableService = new DerivedTableService();
        $tableName = (string) $projectData->csv_derived_table_name;
        $columns = $derivedTableService->getDerivedTableColumns($projectData);
        $derivedTableService->createDerivedTable($tableName, $this->schemaName, $columns);

        $projectData->is_csv_derived_table_created = true;
        $projectData->save();

        $projectDataLogService = new ProjectDataLogService();
        $projectDataLogService->log([
            'project_data_id' => $projectData->id,
            'status_message' => 'Derived table created',
            'job' => 'CreateDerivedTable',
        ]);
    }
}
