<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectData;
use App\Models\ProjectDataCsv;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use League\Csv\Reader;
use League\Csv\Statement;

class CsvTextTableService
{
    public function createCsvTextTable(string $tableName, string $schemaName, array $columns)
    {
        $schemaName = strtolower(trim($schemaName));
        $tableName = strtolower(trim($tableName));

        if ($schemaName === '' || preg_match('/[^a-z0-9_]/', $schemaName) === 1 || strlen($schemaName) > 63) {
            throw new InvalidArgumentException('Invalid schema name: ' . $schemaName);
        }

        if (strlen($tableName) > 55 || preg_match('/^[0-9]/', $tableName) === 1 || preg_match('/[^a-z0-9_]/', $tableName) === 1) {
            throw new InvalidArgumentException('Invalid table name: ' . $tableName);
        }

        $connection = DB::getDefaultConnection();
        if (DB::connection($connection)->getDriverName() !== 'pgsql') {
            throw new InvalidArgumentException('createCsvTextTable only supports pgsql; got ' . DB::connection($connection)->getDriverName());
        }

        // Ensure schema exists.
        DB::connection($connection)->statement('CREATE SCHEMA IF NOT EXISTS "' . $schemaName . '"');

        $qualifiedTable = $schemaName . '.' . $tableName;
        if (Schema::connection($connection)->hasTable($qualifiedTable)) {
            return true;
        }

        Schema::connection($connection)->create($qualifiedTable, function (Blueprint $table) use ($columns) {
            foreach ($columns as $col) {
                if (!is_array($col) || !isset($col['db_column'])) {
                    continue;
                }
                $dbColumn = strtolower(trim((string) $col['db_column']));

                if ($dbColumn === '') {
                    continue;
                }
                if (strlen($dbColumn) > 55) {
                    throw new InvalidArgumentException('Invalid column length > 55: ' . $dbColumn);
                }
                if (preg_match('/^[0-9]/', $dbColumn) === 1) {
                    throw new InvalidArgumentException('Column cannot start with a number: ' . $dbColumn);
                }
                if (preg_match('/[^a-z0-9_]/', $dbColumn) === 1) {
                    throw new InvalidArgumentException('Invalid column name characters: ' . $dbColumn);
                }

                $table->text($dbColumn)->nullable();
            }
        });

        return true;

        
    }
    public function getRecords(ProjectData $projectData)
    {
        if (empty($projectData->url)) {
            return [];
        }

        if ($projectData->type === 'website') {
            return [];
        }

        $url = (string) $projectData->url;
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return [];
        }

        $resolvedPath = null;
        try {
            $candidate = Storage::path($url);
            if (is_string($candidate) && is_file($candidate)) {
                $resolvedPath = $candidate;
            }
        } catch (\Throwable $e) {
            // ignore and fall back
        }
        if ($resolvedPath === null) {
            $candidate = storage_path('app/' . ltrim($url, '/'));
            if (is_file($candidate)) {
                $resolvedPath = $candidate;
            }
        }
        if ($resolvedPath === null) {
            $candidate = storage_path('app/private/' . ltrim($url, '/'));
            if (is_file($candidate)) {
                $resolvedPath = $candidate;
            }
        }
        if ($resolvedPath === null) {
            return [];
        }

        $hasTableType = Schema::hasColumn('project_data_csvs', 'table_type');
        $mappingQuery = ProjectDataCsv::query()->where('project_data_id', (int) $projectData->id);
        if ($hasTableType) {
            $mappingQuery->whereIn('table_type', ['text_table', 'text']);
        }
        $mappings = $mappingQuery->get(['csv_header', 'db_column']);
        if ($mappings->isEmpty()) {
            return [];
        }

        try {
            $csv = Reader::createFromPath($resolvedPath, 'r');
            $csv->setHeaderOffset(0);
            $headers = $csv->getHeader();
        } catch (\Throwable $e) {
            return [];
        }

        $normalize = function (string $value): string {
            $value = trim($value);
            // Strip UTF-8 BOM if present
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
            return strtolower($value);
        };

        $headerLookup = [];
        foreach ($headers as $h) {
            $headerLookup[$normalize((string) $h)] = (string) $h;
        }

        $actualHeaderToDbColumn = [];
        foreach ($mappings as $map) {
            $csvHeader = (string) ($map->csv_header ?? '');
            $dbColumn = (string) ($map->db_column ?? '');
            if ($csvHeader === '' || $dbColumn === '') {
                continue;
            }

            $actualHeader = $headerLookup[$normalize($csvHeader)] ?? null;
            if ($actualHeader === null) {
                continue;
            }
            $actualHeaderToDbColumn[$actualHeader] = $dbColumn;
        }

        if (empty($actualHeaderToDbColumn)) {
            return [];
        }

        $rows = [];
        try {
            $stmt = new Statement();
            foreach ($stmt->process($csv) as $record) {
                if (!is_array($record)) {
                    continue;
                }

                $row = [];
                foreach ($actualHeaderToDbColumn as $actualHeader => $dbColumn) {
                    $row[$dbColumn] = $record[$actualHeader] ?? null;
                }
                $rows[] = $row;
            }
        } catch (\Throwable $e) {
            return [];
        }

        return $rows;
    }
    public function addRecordsToCsvTextTable(string $schemaName, string $tableName, array $records)
    {
        $schemaName = strtolower(trim($schemaName));
        $tableName = strtolower(trim($tableName));

        if ($schemaName === '' || preg_match('/[^a-z0-9_]/', $schemaName) === 1 || strlen($schemaName) > 63) {
            throw new InvalidArgumentException('Invalid schema name: ' . $schemaName);
        }

        if (strlen($tableName) > 55 || preg_match('/^[0-9]/', $tableName) === 1 || preg_match('/[^a-z0-9_]/', $tableName) === 1) {
            throw new InvalidArgumentException('Invalid table name: ' . $tableName);
        }

        if (empty($records)) {
            return 0;
        }

        $connection = DB::getDefaultConnection();
        if (DB::connection($connection)->getDriverName() !== 'pgsql') {
            throw new InvalidArgumentException('addRecordsToCsvTextTable only supports pgsql; got ' . DB::connection($connection)->getDriverName());
        }

        $qualifiedTable = $schemaName . '.' . $tableName;
        if (!Schema::connection($connection)->hasTable($qualifiedTable)) {
            throw new InvalidArgumentException('Target table does not exist: ' . $qualifiedTable);
        }

        $first = $records[0] ?? null;
        if (!is_array($first) || empty($first)) {
            return 0;
        }

        $dbColumns = array_keys($first);
        $dbColumns = array_values(array_filter($dbColumns, function ($col) {
            if (!is_string($col)) {
                return false;
            }
            $col = strtolower(trim($col));
            if ($col === '' || strlen($col) > 55) {
                return false;
            }
            if (preg_match('/^[0-9]/', $col) === 1) {
                return false;
            }
            return preg_match('/[^a-z0-9_]/', $col) !== 1;
        }));

        if (empty($dbColumns)) {
            return 0;
        }

        $normalizeValue = function ($value) {
            if ($value === '') {
                return null;
            }
            if (is_array($value) || is_object($value)) {
                return json_encode($value);
            }
            return $value;
        };

        $rowsToInsert = [];
        foreach ($records as $record) {
            if (!is_array($record)) {
                continue;
            }

            $row = [];
            foreach ($dbColumns as $col) {
                $row[$col] = $normalizeValue($record[$col] ?? null);
            }
            $rowsToInsert[] = $row;
        }

        if (empty($rowsToInsert)) {
            return 0;
        }

        $inserted = 0;
        DB::transaction(function () use ($connection, $qualifiedTable, $rowsToInsert, &$inserted) {
            foreach (array_chunk($rowsToInsert, 500) as $chunk) {
                DB::connection($connection)->table($qualifiedTable)->insert($chunk);
                $inserted += count($chunk);
            }
        });

        return $inserted;
    }
    public function getRecordsForDataTypeIdentification(ProjectData $projectData, string $schemaName = null, string $tableName = null, int $limit = 20): array
    {
        $qualifiedTable = $schemaName . '.' . $tableName;

        $connection = DB::getDefaultConnection();
        if (DB::connection($connection)->getDriverName() !== 'pgsql') {
            throw new InvalidArgumentException('getRecordsForDataTypeIdentification only supports pgsql; got ' . DB::connection($connection)->getDriverName());
        }

        if (!Schema::connection($connection)->hasTable($qualifiedTable)) {
            throw new InvalidArgumentException('Target table does not exist: ' . $qualifiedTable);
        }

        $hasTableType = Schema::hasColumn('project_data_csvs', 'table_type');
        $mappingQuery = ProjectDataCsv::query()->where('project_data_id', (int) $projectData->id);
        if ($hasTableType) {
            $mappingQuery->whereIn('table_type', ['text_table', 'text']);
        }
        $mappings = $mappingQuery->get(['csv_header', 'db_column']);

        $dbColumnToCsvHeader = [];
        foreach ($mappings as $map) {
            $dbColumn = strtolower(trim((string) ($map->db_column ?? '')));
            $csvHeader = (string) ($map->csv_header ?? '');
            if ($dbColumn === '' || $csvHeader === '') {
                continue;
            }
            $dbColumnToCsvHeader[$dbColumn] = $csvHeader;
        }

        return DB::connection($connection)
            ->table($qualifiedTable)
            ->limit($limit)
            ->get()
            ->map(function ($item) use ($dbColumnToCsvHeader) {
                $row = (array) $item;
                if (empty($dbColumnToCsvHeader)) {
                    return $row;
                }

                $mapped = [];
                foreach ($row as $dbColumn => $value) {
                    $lookupKey = strtolower((string) $dbColumn);
                    $csvHeader = $dbColumnToCsvHeader[$lookupKey] ?? (string) $dbColumn;
                    $mapped[$csvHeader] = $value;
                }

                return $mapped;
            })
            ->toArray();
    }
    
}