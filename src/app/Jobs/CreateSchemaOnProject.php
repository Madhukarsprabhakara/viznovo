<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateSchemaOnProject implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $projectId,
        public string $schemaName,
    ) {
        $this->afterCommit = true;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'pgsql') {
            return;
        }

        $schemaName = strtolower($this->schemaName);

        if (preg_match('/^[a-z0-9](?:[a-z0-9_]*[a-z0-9])?_[0-9]+$/', $schemaName) !== 1) {
            throw new InvalidArgumentException('Invalid schema name format: ' . $this->schemaName);
        }

        DB::statement('CREATE SCHEMA IF NOT EXISTS "' . $schemaName . '"');
    }
}
