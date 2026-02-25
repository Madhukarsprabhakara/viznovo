<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeleteSchemaOnProject implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $projectId,
        public string $schemaName,
        public bool $cascade = true,
    ) {
        $this->afterCommit = true;
    }

    public function handle(): void
    {
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'pgsql') {
            return;
        }

        $schemaName = strtolower($this->schemaName);

        if ($schemaName === 'public' || $schemaName === 'information_schema') {
            throw new InvalidArgumentException('Refusing to drop protected schema: ' . $schemaName);
        }

        if (preg_match('/^[a-z0-9](?:[a-z0-9_]*[a-z0-9])?_[0-9]+$/', $schemaName) !== 1) {
            throw new InvalidArgumentException('Invalid schema name format: ' . $this->schemaName);
        }

        $sql = 'DROP SCHEMA IF EXISTS "' . $schemaName . '"' . ($this->cascade ? ' CASCADE' : '');
        DB::statement($sql);
    }
}
