<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Services\DerivedTableService;
use App\Services\ProjectDataLogService;

class AddRecordsDerivedTable implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    protected $schemaName;
    protected $projectData;

    public function __construct(string $schemaName, $projectData)
    {
        $this->schemaName = $schemaName;
        $this->projectData = $projectData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $derivedTableService = new DerivedTableService();
        $tableName = (string) $this->projectData->csv_derived_table_name;
        $records = $derivedTableService->getSourceRecordsForDerivedTable($this->projectData, $this->schemaName);
        $inserted = $derivedTableService->addRecordsDerivedTable($this->schemaName, $tableName, $records);

        $this->projectData->is_csv_derived_table_populated = true;
        $this->projectData->save();

        $projectDataLogService = new ProjectDataLogService();
        $projectDataLogService->log([
            'project_data_id' => $this->projectData->id,
            'status_message' => 'Derived table populated with ' . $inserted . ' records',
            'job' => 'AddRecordsDerivedTable',
        ]);
    }
}
