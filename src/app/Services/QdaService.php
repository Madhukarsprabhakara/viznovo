<?php

namespace App\Services;

use App\Jobs\DerivedColumnChunkProcessing;
use App\Jobs\QdaOpenResponsesFirstChunk;
use App\Jobs\QdaOpenResponsesIncremental;
use App\Models\Project;
use App\Models\ProjectDataCsv;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QdaService
{
    private function resolveUserId($user): ?int
    {
        if (is_object($user) && isset($user->id)) {
            return (int) $user->id;
        }

        if (is_int($user)) {
            return $user;
        }

        if (is_string($user) && ctype_digit($user)) {
            return (int) $user;
        }

        return null;
    }

    private function getLatestDerivedColumnValue(string $connection, string $qualifiedTable, string $derivedColumn, array $existingColumns): string
    {
        if (!in_array($derivedColumn, $existingColumns, true)) {
            return '';
        }

        if (!in_array('updated_at_ts', $existingColumns, true)) {
            return '';
        }

        $latestValue = DB::connection($connection)
            ->table($qualifiedTable)
            ->whereNotNull($derivedColumn)
            ->where($derivedColumn, '!=', '')
            ->orderByDesc('updated_at_ts')
            ->orderByDesc('id')
            ->value($derivedColumn);

        if ($latestValue === null) {
            return '';
        }

        if (is_scalar($latestValue)) {
            return trim((string) $latestValue);
        }

        return '';
    }

    public function createJobs(Project $project, array $openEndedResponseChunks, Report $report, ?string $modelKey = null, $user = null)
    {
        $firstChunkJobs = [];
        // $remainingChunkJobs = [];
        $incrementalChain = [];

        foreach ($openEndedResponseChunks as $tableEntry) {
            if (!is_array($tableEntry)) {
                continue;
            }

            $tableName = $tableEntry['table_name'] ?? null;
            $chunks = $tableEntry['response_chunks'] ?? null;
            $totalChunkCount = is_array($chunks) ? count($chunks) : null;
            if (!is_string($tableName) || $tableName === '') {
                continue;
            }

            if (!is_array($chunks) || $chunks === []) {
                continue;
            }

            $chunks = array_values($chunks);

            $firstChunk = $chunks[0] ?? null;
            if (is_array($firstChunk) && $firstChunk !== []) {
                $firstChunkJobs[] = new QdaOpenResponsesFirstChunk(
                    $project, 
                    $tableName, 
                    0, 
                    array_values($firstChunk), 
                    $report, 
                    $modelKey, 
                    $user,
                    $totalChunkCount,
               );
            }

            $chunkCount = count($chunks);
            $incrementalChain = [];
            for ($chunkIndex = 1; $chunkIndex < $chunkCount; $chunkIndex++) {
                $chunk = $chunks[$chunkIndex] ?? null;
                if (!is_array($chunk) || $chunk === []) {
                    continue;
                }

                $incrementalChain[] = new QdaOpenResponsesIncremental(
                    $project,
                    $tableName,
                    $chunkIndex,
                    array_values($chunk),
                    $report,
                    $modelKey,
                    $user,
                    $totalChunkCount,
                );
            }

            // if ($incrementalChain !== []) {
            //     // Nested arrays create per-table job chains inside a batch.
            //     $remainingChunkJobs[] = $incrementalChain;
            // }
        }

        return [
            'first_chunk_jobs' => $firstChunkJobs,
            'remaining_chunk_jobs' => $incrementalChain,
        ];
    }
    public function createDerivedColumnJobs(Project $project, ?string $modelKey = null, $user = null)
    {
        $jobs = [];
        $userId = $this->resolveUserId($user);

        $schemaName = strtolower(trim((string) ($project->schema_name ?: Project::makeSchemaName((string) $project->name, (int) $project->id))));
        if ($schemaName === '' || preg_match('/[^a-z0-9_]/', $schemaName) === 1 || strlen($schemaName) > 63) {
            return $jobs;
        }

        $connection = DB::getDefaultConnection();
        if (DB::connection($connection)->getDriverName() !== 'pgsql') {
            return $jobs;
        }

        $projectDataList = $project->derivedTables()->get();

        foreach ($projectDataList as $projectData) {
            $tableName = strtolower(trim((string) ($projectData->csv_derived_table_name ?? '')));
            if ($tableName === '' || preg_match('/^[0-9]/', $tableName) === 1 || preg_match('/[^a-z0-9_]/', $tableName) === 1 || strlen($tableName) > 55) {
                continue;
            }

            $qualifiedTable = $schemaName . '.' . $tableName;
            if (!Schema::connection($connection)->hasTable($qualifiedTable)) {
                continue;
            }

            $existingColumns = DB::connection($connection)
                ->table('information_schema.columns')
                ->where('table_schema', $schemaName)
                ->where('table_name', $tableName)
                ->pluck('column_name')
                ->map(function ($column) {
                    return strtolower(trim((string) $column));
                })
                ->filter()
                ->values()
                ->toArray();

            if (!in_array('id', $existingColumns, true)) {
                continue;
            }

            $derivedColumns = ProjectDataCsv::query()
                ->where('project_data_id', (int) $projectData->id)
                ->where('table_type', 'derived_table')
                ->whereNotNull('db_column')
                ->whereNotNull('derived_db_column')
                ->select(['db_column', 'derived_db_column', 'prompt_instructions'])
                ->orderBy('id')
                ->get()
                ->map(function (ProjectDataCsv $row) {
                    return [
                        'db_column' => strtolower(trim((string) ($row->db_column ?? ''))),
                        'derived_db_column' => strtolower(trim((string) ($row->derived_db_column ?? ''))),
                        'prompt' => trim((string) ($row->prompt_instructions ?? '')),
                    ];
                })
                ->filter(function (array $row) {
                    foreach (['db_column', 'derived_db_column'] as $key) {
                        $column = $row[$key] ?? '';
                        if (!is_string($column) || $column === '' || strlen($column) > 55) {
                            return false;
                        }

                        if (preg_match('/^[0-9]/', $column) === 1) {
                            return false;
                        }

                        if (preg_match('/[^a-z0-9_]/', $column) === 1) {
                            return false;
                        }
                    }

                    return true;
                })
                ->unique(function (array $row) {
                    return ($row['db_column'] ?? '') . '|' . ($row['derived_db_column'] ?? '');
                })
                ->values();

            foreach ($derivedColumns as $derivedColumn) {
                $sourceColumn = $derivedColumn['db_column'];
                $derivedDbColumn = $derivedColumn['derived_db_column'];

                if (!in_array($sourceColumn, $existingColumns, true)) {
                    continue;
                }

                $previousCategories = $this->getLatestDerivedColumnValue($connection, $qualifiedTable, $derivedDbColumn, $existingColumns);

                $records = DB::connection($connection)
                    ->table($qualifiedTable)
                    ->select(['id', $sourceColumn])
                    ->orderBy('id')
                    ->get()
                    ->map(function ($row) use ($sourceColumn) {
                        return [
                            'id' => isset($row->id) ? (int) $row->id : null,
                            $sourceColumn => $row->{$sourceColumn} ?? null,
                        ];
                    })
                    ->filter(function (array $row) {
                        return $row['id'] !== null;
                    })
                    ->values()
                    ->toArray();

                if ($records === []) {
                    continue;
                }

                $recordChunks = array_values(array_chunk($records, 20));
                $totalChunks = count($recordChunks);
                
                foreach ($recordChunks as $index => $chunk) {
                    
                    $jobs[] = new DerivedColumnChunkProcessing([
                        'prompt' => $derivedColumn['prompt'],
                        'derived_db_column' => $derivedDbColumn,
                        'db_column' => $sourceColumn,
                        'records' => array_values($chunk),
                    ], $schemaName, $tableName, (int) $projectData->id, $userId, $modelKey, $index + 1, $totalChunks, $previousCategories ?? '');
                }
            }
        }
        
        return $jobs;
    }
    
    
}