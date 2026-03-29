<?php

namespace App\Services;

use App\Models\CsvDataType;
use App\Models\ProjectDataCsv;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\ProjectData;
use InvalidArgumentException;
use App\Models\Project;
use App\Jobs\CreateDerivedTable;
use App\Jobs\AddRecordsDerivedTable;
use App\Jobs\DispatchDerivedColumnBatch;

class DerivedTableService
{
    public function storeDerivedColumns(array $derivedTables, int $userId, ?int $reportId = null): array
    {
        $rows = [];
        $now = now();

        foreach ($derivedTables as $derivedTable) {
            if (!is_array($derivedTable)) {
                continue;
            }

            $projectDataId = isset($derivedTable['project_data_id']) ? (int) $derivedTable['project_data_id'] : null;
            if (!$projectDataId) {
                continue;
            }

            foreach (($derivedTable['table_schema'] ?? []) as $column) {
                if (!is_array($column)) {
                    continue;
                }

                $dataTypeKey = trim((string) ($column['data_type'] ?? ''));
                $derivedDbColumn = trim((string) ($column['derived_db_column'] ?? ''));

                if ($dataTypeKey === '' || $derivedDbColumn === '') {
                    continue;
                }

                $rows[] = [
                    'project_data_id' => $projectDataId,
                    'csv_data_type_id' => $this->getCsvDataTypeId($dataTypeKey),
                    'user_id' => $userId,
                    'csv_header' => trim((string) ($column['csv_header'] ?? '')),
                    'db_column' => trim((string) ($column['db_column'] ?? '')),
                    'derived_csv_header' => trim((string) ($column['derived_csv_header'] ?? '')),
                    'derived_db_column' => $derivedDbColumn,
                    'prompt_instructions' => (string) ($column['prompt_instructions'] ?? ''),
                    'table_type' => 'derived_table',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows === []) {
            return [];
        }

        DB::transaction(function () use ($rows): void {
            $projectDataIds = array_values(array_unique(array_map(
                static fn (array $row): int => (int) $row['project_data_id'],
                $rows
            )));

            ProjectDataCsv::query()
                ->whereIn('project_data_id', $projectDataIds)
                ->where('table_type', 'derived_table')
                ->delete();

            foreach (array_chunk($rows, 500) as $chunk) {
                ProjectDataCsv::query()->insert($chunk);
            }
        });

        return $rows;
    }

    private function getCsvDataTypeId(string $key): int
    {
        $type = CsvDataType::query()->where('csv_type_key', $key)->first();
        if ($type) {
            return (int) $type->id;
        }

        $created = CsvDataType::query()->create([
            'csv_type_key' => $key,
            'db_type' => 'text',
            'laravel_type' => 'text',
        ]);

        if (!$created) {
            throw new InvalidArgumentException('Unable to resolve csv_data_types row for ' . $key);
        }

        return (int) $created->id;
    }
    public function getRecordsForDerivedTable(string $schemaName, string $tableName): array
    {
        return (new CsvDTTableService())->getRecords($schemaName, $tableName);
    }
    public function createDerivedTable(string $tableName, string $schemaName, array $columns)
    {
        return (new CsvDTTableService())->createCsvDataTypeTable($tableName, $schemaName, $columns, true);
    }
    public function addRecordsDerivedTable(string $schemaName, string $tableName, array $records)
    {
        return (new CsvDTTableService())->addRecordsToCsvDataTypeTable($schemaName, $tableName, $records);
    }
    public function getSourceRecordsForDerivedTable(ProjectData $projectData, string $schemaName): array
    {
        $sourceTableName = strtolower(trim((string) ($projectData->csv_data_type_table_name ?? '')));

        if ($sourceTableName === '') {
            throw new InvalidArgumentException('Source dt table name is missing for project data ' . $projectData->id);
        }

        return (new CsvDTTableService())->getRecords($schemaName, $sourceTableName);
    }
    public function getDerivedTableColumns(ProjectData $projectData): array
    {
        $projectDataId = (int) $projectData->id;

        $dtColumnsQuery = DB::table('project_data_csvs as pdc')
            ->leftJoin('csv_data_types as cdt', 'pdc.csv_data_type_id', '=', 'cdt.id')
            ->where('pdc.project_data_id', $projectDataId)
            ->whereNotNull('pdc.db_column')
            ->select([
                'pdc.db_column as db_column',
                'cdt.laravel_type as laravel_type',
            ]);

        $derivedColumnsQuery = DB::table('project_data_csvs as pdc')
            ->leftJoin('csv_data_types as cdt', 'pdc.csv_data_type_id', '=', 'cdt.id')
            ->where('pdc.project_data_id', $projectDataId)
            ->whereNotNull('pdc.derived_db_column')
            ->select([
                'pdc.derived_db_column as db_column',
                'cdt.laravel_type as laravel_type',
            ]);

        if (Schema::hasColumn('project_data_csvs', 'table_type')) {
            $dtColumnsQuery->where('pdc.table_type', 'dt_table');
            $derivedColumnsQuery->where('pdc.table_type', 'derived_table');
        }

        return $dtColumnsQuery
            ->get()
            ->concat($derivedColumnsQuery->get())
            ->map(function ($row) {
                return [
                    'db_column' => strtolower(trim((string) ($row->db_column ?? ''))),
                    'laravel_type' => isset($row->laravel_type) ? (string) $row->laravel_type : null,
                ];
            })
            ->filter(function (array $row) {
                $dbColumn = $row['db_column'];

                if ($dbColumn === '' || $dbColumn === 'id') {
                    return false;
                }

                if (strlen($dbColumn) > 55) {
                    return false;
                }

                if (preg_match('/^[0-9]/', $dbColumn) === 1) {
                    return false;
                }

                return preg_match('/[^a-z0-9_]/', $dbColumn) !== 1;
            })
            ->unique('db_column')
            ->values()
            ->toArray();
    }
    public function createDJobs(Project $project, ?string $modelKey = null, ?int $userId = null, ?int $reportId = null, ?string $prompt = null, mixed $qualitativeDataRaw = null): array
    {
        $projectDataList = $project->derivedTables()->get();
        $jobs = [];

        if ($projectDataList->isEmpty()) {
            return $jobs;
        }

        foreach ($projectDataList as $projectData) {
            $jobs[] = new CreateDerivedTable($project->schema_name, (int) $projectData->id);
            $jobs[] = new AddRecordsDerivedTable($project->schema_name, (int) $projectData->id);
        }

        $jobs[] = new DispatchDerivedColumnBatch((int) $project->id, $userId, $modelKey, $reportId, $prompt, $qualitativeDataRaw);

        return $jobs;
    }

    public function storeDerivedData(mixed $decoded, string $schemaName, string $tableName, array $chunk): int
    {
        $schemaName = $this->normalizeIdentifier($schemaName, 63, true);
        $tableName = $this->normalizeIdentifier($tableName, 55);
        $derivedColumn = $this->normalizeIdentifier((string) ($chunk['derived_db_column'] ?? ''), 55);

        $connection = DB::getDefaultConnection();
        if (DB::connection($connection)->getDriverName() !== 'pgsql') {
            throw new InvalidArgumentException('storeDerivedData only supports pgsql; got ' . DB::connection($connection)->getDriverName());
        }

        $qualifiedTable = $schemaName . '.' . $tableName;
        if (!Schema::connection($connection)->hasTable($qualifiedTable)) {
            throw new InvalidArgumentException('Target table does not exist: ' . $qualifiedTable);
        }

        $columnNames = DB::connection($connection)
            ->table('information_schema.columns')
            ->where('table_schema', $schemaName)
            ->where('table_name', $tableName)
            ->pluck('column_name')
            ->map(function ($columnName) {
                return strtolower(trim((string) $columnName));
            })
            ->values()
            ->toArray();

        if (!in_array('id', $columnNames, true)) {
            throw new InvalidArgumentException('Target table does not have an id column: ' . $qualifiedTable);
        }

        if (!in_array($derivedColumn, $columnNames, true)) {
            throw new InvalidArgumentException('Derived column does not exist on target table: ' . $qualifiedTable . '.' . $derivedColumn);
        }

        $updates = $this->extractDerivedUpdates($decoded, $chunk, $derivedColumn);

        if ($updates === []) {
            Log::warning('Derived column chunk returned no usable row updates.', [
                'schema_name' => $schemaName,
                'table_name' => $tableName,
                'derived_db_column' => $derivedColumn,
                'decoded' => $decoded,
            ]);

            return 0;
        }

        $updatedRows = 0;
        foreach ($updates as $id => $value) {
            try {
                $affected = DB::connection($connection)
                    ->table($qualifiedTable)
                    ->where('id', $id)
                    ->update([$derivedColumn => $this->normalizeDerivedValue($value)]);

                $updatedRows += (int) $affected;
            } catch (\Throwable $exception) {
                Log::warning('Failed to update derived row from AI output.', [
                    'schema_name' => $schemaName,
                    'table_name' => $tableName,
                    'derived_db_column' => $derivedColumn,
                    'row_id' => $id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $updatedRows;
    }

    private function normalizeIdentifier(string $value, int $maxLength, bool $allowLeadingUnderscore = false): string
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            throw new InvalidArgumentException('Identifier cannot be empty.');
        }

        if (strlen($value) > $maxLength) {
            throw new InvalidArgumentException('Identifier exceeds maximum length: ' . $value);
        }

        $pattern = $allowLeadingUnderscore ? '/^[a-z_][a-z0-9_]*$/' : '/^[a-z][a-z0-9_]*$/';
        if (preg_match($pattern, $value) !== 1) {
            throw new InvalidArgumentException('Invalid identifier: ' . $value);
        }

        return $value;
    }

    /**
     * @return array<int, mixed>
     */
    private function extractDerivedUpdates(mixed $decoded, array $chunk, string $derivedColumn): array
    {
        $payload = $this->unwrapDecodedPayload($decoded);
        $records = [];

        if (is_array($payload) && isset($payload['records']) && is_array($payload['records'])) {
            $records = $payload['records'];
        } elseif (is_array($payload) && array_is_list($payload)) {
            $records = $payload;
        }

        if ($records === []) {
            return [];
        }

        $sourceIds = collect($chunk['records'] ?? [])
            ->pluck('id')
            ->filter(function ($id) {
                return filter_var($id, FILTER_VALIDATE_INT) !== false;
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $allowedIds = array_fill_keys($sourceIds, true);
        $updates = [];

        foreach ($records as $record) {
            if (!is_array($record)) {
                continue;
            }

            $id = $record['id'] ?? null;
            if (filter_var($id, FILTER_VALIDATE_INT) === false) {
                continue;
            }

            $id = (int) $id;
            if ($allowedIds !== [] && !isset($allowedIds[$id])) {
                continue;
            }

            if (array_key_exists($derivedColumn, $record)) {
                $updates[$id] = $record[$derivedColumn];
                continue;
            }

            if (array_key_exists('derived_db_column', $record)) {
                $updates[$id] = $record['derived_db_column'];
                continue;
            }

            $candidateKeys = array_values(array_filter(array_keys($record), function (string $key) {
                return $key !== 'id';
            }));

            if (count($candidateKeys) === 1) {
                $updates[$id] = $record[$candidateKeys[0]];
            }
        }

        return $updates;
    }

    private function unwrapDecodedPayload(mixed $decoded): mixed
    {
        if (!is_array($decoded)) {
            return $decoded;
        }

        if (array_key_exists('prompt_response', $decoded) && is_string($decoded['prompt_response'])) {
            $nested = json_decode($decoded['prompt_response'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->unwrapDecodedPayload($nested);
            }
        }

        if (array_is_list($decoded)) {
            foreach ($decoded as $item) {
                if (!is_array($item)) {
                    continue;
                }

                if (isset($item['records']) && is_array($item['records'])) {
                    return $item;
                }

                if (array_key_exists('prompt_response', $item) && is_string($item['prompt_response'])) {
                    $nested = json_decode($item['prompt_response'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $this->unwrapDecodedPayload($nested);
                    }
                }
            }
        }

        return $decoded;
    }

    private function normalizeDerivedValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value) && (is_nan($value) || is_infinite($value))) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            try {
                return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return null;
            }
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed === '' ? null : $trimmed;
        }

        return $value;
    }
}