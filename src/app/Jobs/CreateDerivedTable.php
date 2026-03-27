<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Services\DerivedTableService;
use App\Services\ProjectDataLogService;

class CreateDerivedTable implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    protected $projectData;
    protected $schemaName;
    public function __construct(string $schemaName, $projectData)
    {
        //
        $this->projectData = $projectData;
        $this->schemaName = $schemaName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $derivedTableService = new DerivedTableService();
        $tableName = (string) $this->projectData->csv_derived_table_name;
        $columns = $derivedTableService->getDerivedTableColumns($this->projectData);
        $derivedTableService->createDerivedTable($tableName, $this->schemaName, $columns);

        $this->projectData->is_csv_derived_table_created = true;
        $this->projectData->save();

        $projectDataLogService = new ProjectDataLogService();
        $projectDataLogService->log([
            'project_data_id' => $this->projectData->id,
            'status_message' => 'Derived table created',
            'job' => 'CreateDerivedTable',
        ]);
    }
}
