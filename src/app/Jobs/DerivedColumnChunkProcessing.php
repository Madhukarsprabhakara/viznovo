<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;

class DerivedColumnChunkProcessing implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    protected array $chunk;
    protected string $schemaName;
    protected string $tableName;
    public function __construct(array $chunk, string $schemaName, string $tableName)
    {
        $this->chunk = $chunk;
        $this->schemaName = $schemaName;
        $this->tableName = $tableName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        dd($this->chunk);

    }
}
