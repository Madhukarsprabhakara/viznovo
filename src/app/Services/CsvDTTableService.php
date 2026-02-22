<?php

namespace App\Services;

use App\Models\ProjectData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class CsvDTTableService
{

    /**
     * @return array<int, array{db_column:string, laravel_type:?string}>
     */
    public function getCsvDataTypeTableColumns(ProjectData $projectData): array
    {
        $query = DB::table('project_data_csvs as pdc')
            ->leftJoin('csv_data_types as cdt', 'pdc.csv_data_type_id', '=', 'cdt.id')
            ->where('pdc.project_data_id', (int) $projectData->id)
            ->whereNotNull('pdc.db_column')
            ->select([
                'pdc.db_column as db_column',
                'cdt.laravel_type as laravel_type',
            ]);

        if (Schema::hasColumn('project_data_csvs', 'table_type')) {
            $query->where('pdc.table_type', 'dt_table');
        }

        return $query
            ->orderBy('pdc.id')
            ->get()
            ->map(function ($row) {
                return [
                    'db_column' => (string) ($row->db_column ?? ''),
                    'laravel_type' => isset($row->laravel_type) ? (string) $row->laravel_type : null,
                ];
            })
            ->filter(function (array $row) {
                return $row['db_column'] !== '';
            })
            ->values()
            ->toArray();
    }
    public function createCsvDataTypeTable(string $tableName, string $schemaName, array $columns)
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
            throw new InvalidArgumentException('createCsvDataTypeTable only supports pgsql; got ' . DB::connection($connection)->getDriverName());
        }

        DB::connection($connection)->statement('CREATE SCHEMA IF NOT EXISTS "' . $schemaName . '"');

        $qualifiedTable = $schemaName . '.' . $tableName;
        if (Schema::connection($connection)->hasTable($qualifiedTable)) {
            return true;
        }

        $typeInfoByLaravelType = DB::table('csv_data_types')
            ->select(['laravel_type', 'db_type'])
            ->whereNotNull('laravel_type')
            ->get()
            ->reduce(function (array $carry, $row) {
                $laravelType = strtolower(trim((string) ($row->laravel_type ?? '')));
                if ($laravelType === '') {
                    return $carry;
                }
                $carry[$laravelType] = strtolower(trim((string) ($row->db_type ?? '')));
                return $carry;
            }, []);

        // Guardrail: only allow column-creating Blueprint methods.
        $safeBlueprintTypes = [
            'string', 'text', 'longtext', 'mediumtext',
            'integer', 'biginteger',
            'float', 'double', 'decimal',
            'boolean',
            'date', 'datetime', 'timestamp',
            'json', 'uuid',
        ];

        Schema::connection($connection)->create($qualifiedTable, function (Blueprint $table) use ($columns, $typeInfoByLaravelType, $safeBlueprintTypes) {
            foreach ($columns as $col) {
                if (!is_array($col)) {
                    continue;
                }

                $dbColumn = strtolower(trim((string) ($col['db_column'] ?? '')));
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

                $laravelType = strtolower(trim((string) ($col['laravel_type'] ?? '')));

                if ($laravelType === '' || !array_key_exists($laravelType, $typeInfoByLaravelType)) {
                    $laravelType = 'text';
                }

                $dbType = $typeInfoByLaravelType[$laravelType] ?? '';

                // Normalize common variants to Blueprint method names.
                if ($laravelType === 'biginteger') {
                    $laravelType = 'bigInteger';
                } elseif ($laravelType === 'datetime') {
                    $laravelType = 'dateTime';
                } elseif ($laravelType === 'longtext') {
                    $laravelType = 'longText';
                } elseif ($laravelType === 'mediumtext') {
                    $laravelType = 'mediumText';
                }

                $safeKey = strtolower($laravelType);
                if (!in_array($safeKey, $safeBlueprintTypes, true)) {
                    $table->text($dbColumn)->nullable();
                    continue;
                }

                if ($dbType === 'decimal' || $safeKey === 'decimal') {
                    $table->decimal($dbColumn, 18, 6)->nullable();
                    continue;
                }

                if (method_exists($table, $laravelType)) {
                    $column = $table->{$laravelType}($dbColumn);
                    if (is_object($column)) {
                        // ColumnDefinition uses magic methods; always mark nullable.
                        $column->nullable();
                    }
                } else {
                    $table->text($dbColumn)->nullable();
                }
            }
        });

        return true;
    }
    public function getRecords(string $schemaName, string $tableName): array
    {
        $schemaName = strtolower(trim($schemaName));
        $tableName = strtolower(trim($tableName));

        if ($schemaName === '' || preg_match('/[^a-z0-9_]/', $schemaName) === 1 || strlen($schemaName) > 63) {
            throw new InvalidArgumentException('Invalid schema name: ' . $schemaName);
        }

        if (strlen($tableName) > 55 || preg_match('/^[0-9]/', $tableName) === 1 || preg_match('/[^a-z0-9_]/', $tableName) === 1) {
            throw new InvalidArgumentException('Invalid table name: ' . $tableName);
        }

        $qualifiedTable = $schemaName . '.' . $tableName;

        $connection = DB::getDefaultConnection();
        if (DB::connection($connection)->getDriverName() !== 'pgsql') {
            throw new InvalidArgumentException('getRecords only supports pgsql; got ' . DB::connection($connection)->getDriverName());
        }

        if (!Schema::connection($connection)->hasTable($qualifiedTable)) {
            throw new InvalidArgumentException('Target table does not exist: ' . $qualifiedTable);
        }

        return DB::connection($connection)
            ->table($qualifiedTable)
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }
    public function addRecordsToCsvDataTypeTable(string $schemaName, string $tableName, array $records)
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
            throw new InvalidArgumentException('addRecordsToCsvDataTypeTable only supports pgsql; got ' . DB::connection($connection)->getDriverName());
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
    
}