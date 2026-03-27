<?php

namespace App\Services;

use App\Models\ProjectData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use App\Models\Project;
class CsvDTTableService
{

    /**
     * JSON encoding in PHP will fail if the payload contains INF/-INF/NaN floats.
     * Normalize those values (and nested arrays) to be JSON-safe.
     *
     * @param mixed $value
     * @return mixed
     */
    private function normalizeForJson($value)
    {
        if (is_float($value)) {
            if (is_nan($value) || is_infinite($value)) {
                return null;
            }
            return $value;
        }

        if (is_object($value)) {
            $value = (array) $value;
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->normalizeForJson($v);
            }
            return $value;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeRowForJson(array $row): array
    {
        foreach ($row as $k => $v) {
            $row[$k] = $this->normalizeForJson($v);
        }
        return $row;
    }

    private function normalizeColumnName($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = strtolower(trim($value));
        if ($value === '' || strlen($value) > 55) {
            return null;
        }

        if (preg_match('/^[0-9]/', $value) === 1) {
            return null;
        }

        if (preg_match('/[^a-z0-9_]/', $value) === 1) {
            return null;
        }

        return $value;
    }

    private function isNullLikeValue($value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_float($value) && (is_nan($value) || is_infinite($value))) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        $normalized = strtolower(trim($value));

        return in_array($normalized, ['', '?', 'n/a', 'na', 'null', 'nil', 'none', 'nan', 'inf', '-inf', 'infinity', '-infinity'], true);
    }

    private function sanitizeStringValue($value): ?string
    {
        if ($this->isNullLikeValue($value)) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return $this->encodeJsonValue($value);
        }

        $stringValue = (string) $value;

        return trim($stringValue) === '' ? null : $stringValue;
    }

    private function sanitizeBooleanValue($value): ?bool
    {
        if ($this->isNullLikeValue($value)) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            if ($value === 1) {
                return true;
            }

            if ($value === 0) {
                return false;
            }

            return null;
        }

        if (is_float($value)) {
            if (is_nan($value) || is_infinite($value)) {
                return null;
            }

            if ($value === 1.0) {
                return true;
            }

            if ($value === 0.0) {
                return false;
            }

            return null;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 't', 'yes', 'y'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'f', 'no', 'n'], true)) {
            return false;
        }

        return null;
    }

    private function sanitizeIntegerValue($value)
    {
        if ($this->isNullLikeValue($value)) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            if (is_nan($value) || is_infinite($value) || floor($value) !== $value) {
                return null;
            }

            return (int) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $cleaned = preg_replace('/[,\s]+/', '', trim($value));
        if ($cleaned === null || $cleaned === '') {
            return null;
        }

        if (preg_match('/^[+-]?\d+$/', $cleaned) !== 1) {
            return null;
        }

        return $cleaned;
    }

    private function sanitizeNumericValue($value)
    {
        if ($this->isNullLikeValue($value)) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            if (is_nan($value) || is_infinite($value)) {
                return null;
            }

            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $cleaned = preg_replace('/[,\s]+/', '', trim($value));
        if ($cleaned === null || $cleaned === '' || !is_numeric($cleaned)) {
            return null;
        }

        $numericValue = $cleaned + 0;
        if (is_float($numericValue) && (is_nan($numericValue) || is_infinite($numericValue))) {
            return null;
        }

        return $cleaned;
    }

    private function encodeJsonValue($value): ?string
    {
        if ($this->isNullLikeValue($value)) {
            return null;
        }

        try {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return json_encode(
                        $this->normalizeForJson($decoded),
                        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
                    );
                }
            }

            return json_encode(
                $this->normalizeForJson($value),
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $exception) {
            return null;
        }
    }

    private function parseDateTimeValue($value): ?\DateTimeImmutable
    {
        if ($this->isNullLikeValue($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }

        if (!is_string($value)) {
            return null;
        }

        $stringValue = trim($value);
        if ($stringValue === '') {
            return null;
        }

        $formats = [
            '!Y-m-d H:i:s',
            '!Y-m-d H:i',
            '!Y-m-d\\TH:i:s.uP',
            '!Y-m-d\\TH:i:sP',
            '!Y-m-d\\TH:i:s',
            '!Y-m-d',
            '!Y/m/d H:i:s',
            '!Y/m/d H:i',
            '!Y/m/d',
            '!m/d/Y H:i:s',
            '!n/j/Y H:i:s',
            '!m/d/Y H:i',
            '!n/j/Y H:i',
            '!m/d/Y',
            '!n/j/Y',
            '!d/m/Y H:i:s',
            '!j/n/Y H:i:s',
            '!d/m/Y H:i',
            '!j/n/Y H:i',
            '!d/m/Y',
            '!j/n/Y',
            '!m-d-Y',
            '!n-j-Y',
            '!d-m-Y',
            '!j-n-Y',
        ];

        foreach ($formats as $format) {
            $parsed = \DateTimeImmutable::createFromFormat($format, $stringValue);
            if ($parsed === false) {
                continue;
            }

            $errors = \DateTimeImmutable::getLastErrors();
            if ($errors === false || (($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0)) {
                return $parsed;
            }
        }

        $timestamp = strtotime($stringValue);
        if ($timestamp === false) {
            return null;
        }

        return (new \DateTimeImmutable())->setTimestamp($timestamp);
    }

    private function sanitizeDateValue($value): ?string
    {
        $dateTime = $this->parseDateTimeValue($value);

        return $dateTime?->format('Y-m-d');
    }

    private function sanitizeTimestampValue($value): ?string
    {
        $dateTime = $this->parseDateTimeValue($value);

        return $dateTime?->format('Y-m-d H:i:s');
    }

    private function sanitizeUuidValue($value): ?string
    {
        if ($this->isNullLikeValue($value)) {
            return null;
        }

        $stringValue = trim((string) $value);
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $stringValue) !== 1) {
            return null;
        }

        return strtolower($stringValue);
    }

    /**
     * @return array<string, array{column_name:string,data_type:string,udt_name:string,is_nullable:bool,has_default:bool}>
     */
    private function getTableColumnMetadata(string $connection, string $schemaName, string $tableName): array
    {
        return DB::connection($connection)
            ->table('information_schema.columns')
            ->where('table_schema', $schemaName)
            ->where('table_name', $tableName)
            ->orderBy('ordinal_position')
            ->get([
                'column_name',
                'data_type',
                'udt_name',
                'is_nullable',
                'column_default',
            ])
            ->mapWithKeys(function ($column) {
                $columnName = strtolower(trim((string) ($column->column_name ?? '')));
                if ($columnName === '') {
                    return [];
                }

                return [
                    $columnName => [
                        'column_name' => $columnName,
                        'data_type' => strtolower(trim((string) ($column->data_type ?? ''))),
                        'udt_name' => strtolower(trim((string) ($column->udt_name ?? ''))),
                        'is_nullable' => strtoupper((string) ($column->is_nullable ?? 'YES')) === 'YES',
                        'has_default' => $column->column_default !== null,
                    ],
                ];
            })
            ->toArray();
    }

    private function sanitizeValueForColumn($value, array $columnMeta)
    {
        $dataType = strtolower(trim((string) ($columnMeta['data_type'] ?? '')));
        $udtName = strtolower(trim((string) ($columnMeta['udt_name'] ?? '')));

        if (in_array($dataType, ['json', 'jsonb'], true) || in_array($udtName, ['json', 'jsonb'], true)) {
            return $this->encodeJsonValue($value);
        }

        if ($dataType === 'date') {
            return $this->sanitizeDateValue($value);
        }

        if (in_array($dataType, ['timestamp without time zone', 'timestamp with time zone'], true) || in_array($udtName, ['timestamp', 'timestamptz'], true)) {
            return $this->sanitizeTimestampValue($value);
        }

        if ($dataType === 'boolean' || $udtName === 'bool') {
            return $this->sanitizeBooleanValue($value);
        }

        if (in_array($dataType, ['smallint', 'integer', 'bigint'], true) || in_array($udtName, ['int2', 'int4', 'int8'], true)) {
            return $this->sanitizeIntegerValue($value);
        }

        if (in_array($dataType, ['numeric', 'decimal', 'real', 'double precision'], true) || in_array($udtName, ['numeric', 'float4', 'float8'], true)) {
            return $this->sanitizeNumericValue($value);
        }

        if ($dataType === 'uuid' || $udtName === 'uuid') {
            return $this->sanitizeUuidValue($value);
        }

        return $this->sanitizeStringValue($value);
    }

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
    public function createCsvDataTypeTable(string $tableName, string $schemaName, array $columns, bool $recreateIfExists = false)
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
            if ($recreateIfExists) {
                DB::connection($connection)->statement('DROP TABLE IF EXISTS "' . $schemaName . '"."' . $tableName . '"');
            } else {
                return true;
            }
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
            $table->id();

            foreach ($columns as $col) {
                if (!is_array($col)) {
                    continue;
                }

                $dbColumn = strtolower(trim((string) ($col['db_column'] ?? '')));
                if ($dbColumn === '') {
                    continue;
                }
                if ($dbColumn === 'id') {
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

        $hasIdColumn = DB::connection($connection)
            ->table('information_schema.columns')
            ->where('table_schema', $schemaName)
            ->where('table_name', $tableName)
            ->where('column_name', 'id')
            ->exists();

        $query = DB::connection($connection)->table($qualifiedTable);

        if ($hasIdColumn) {
            $query->orderBy('id');
        }

        return $query
            ->get()
            ->map(function ($item) {
                return $this->normalizeRowForJson((array) $item);
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

        $columnMetadata = $this->getTableColumnMetadata($connection, $schemaName, $tableName);
        if (empty($columnMetadata)) {
            throw new InvalidArgumentException('Unable to inspect target table columns: ' . $qualifiedTable);
        }

        $requiredColumns = array_keys(array_filter($columnMetadata, function (array $columnMeta) {
            return !$columnMeta['is_nullable'] && !$columnMeta['has_default'];
        }));

        $rowsBySignature = [];
        $skippedRows = 0;

        foreach ($records as $record) {
            if (!is_array($record)) {
                $skippedRows++;
                continue;
            }

            $row = [];
            foreach ($record as $rawColumn => $rawValue) {
                $columnName = $this->normalizeColumnName($rawColumn);
                if ($columnName === null || !array_key_exists($columnName, $columnMetadata)) {
                    continue;
                }
                if ($columnName === 'id') {
                    continue;
                }

                $sanitizedValue = $this->sanitizeValueForColumn($rawValue, $columnMetadata[$columnName]);
                if ($sanitizedValue === null) {
                    continue;
                }

                $row[$columnName] = $sanitizedValue;
            }

            foreach ($requiredColumns as $requiredColumn) {
                if (!array_key_exists($requiredColumn, $row)) {
                    $skippedRows++;
                    continue 2;
                }
            }

            if (empty($row)) {
                $skippedRows++;
                continue;
            }

            ksort($row);
            $signature = implode('|', array_keys($row));
            $rowsBySignature[$signature][] = $row;
        }

        if (empty($rowsBySignature)) {
            return 0;
        }

        $inserted = 0;
        $failedRows = 0;

        foreach ($rowsBySignature as $rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                try {
                    DB::connection($connection)->table($qualifiedTable)->insert($chunk);
                    $inserted += count($chunk);
                    continue;
                } catch (\Throwable $exception) {
                    Log::warning('CsvDTTableService batch insert failed; retrying rows individually.', [
                        'schema_name' => $schemaName,
                        'table_name' => $tableName,
                        'chunk_size' => count($chunk),
                        'error' => $exception->getMessage(),
                    ]);
                }

                foreach ($chunk as $row) {
                    try {
                        DB::connection($connection)->table($qualifiedTable)->insert($row);
                        $inserted++;
                    } catch (\Throwable $rowException) {
                        $failedRows++;

                        Log::warning('CsvDTTableService failed to insert sanitized row.', [
                            'schema_name' => $schemaName,
                            'table_name' => $tableName,
                            'columns' => array_keys($row),
                            'error' => $rowException->getMessage(),
                        ]);
                    }
                }
            }
        }

        if ($skippedRows > 0 || $failedRows > 0) {
            Log::warning('CsvDTTableService inserted records with some rows skipped or rejected.', [
                'schema_name' => $schemaName,
                'table_name' => $tableName,
                'received_rows' => count($records),
                'inserted_rows' => $inserted,
                'skipped_rows' => $skippedRows,
                'failed_rows' => $failedRows,
            ]);
        }

        return $inserted;
    }
    public function getDataTypeTableRecords($schemaName, $tableName)
    {
        $schemaName = strtolower(trim((string) $schemaName));
        $tableName = strtolower(trim((string) $tableName));

        if ($schemaName === '' || preg_match('/[^a-z0-9_]/', $schemaName) === 1 || strlen($schemaName) > 63) {
            throw new InvalidArgumentException('Invalid schema name: ' . $schemaName);
        }

        if (strlen($tableName) > 55 || preg_match('/^[0-9]/', $tableName) === 1 || preg_match('/[^a-z0-9_]/', $tableName) === 1) {
            throw new InvalidArgumentException('Invalid table name: ' . $tableName);
        }

        $qualifiedTable = $schemaName . '.' . $tableName;

        $connection = DB::getDefaultConnection();
        if (DB::connection($connection)->getDriverName() !== 'pgsql') {
            throw new InvalidArgumentException('getDataTypeTableRecords only supports pgsql; got ' . DB::connection($connection)->getDriverName());
        }

        if (!Schema::connection($connection)->hasTable($qualifiedTable)) {
            throw new InvalidArgumentException('Target table does not exist: ' . $qualifiedTable);
        }

        $hasIdColumn = DB::connection($connection)
            ->table('information_schema.columns')
            ->where('table_schema', $schemaName)
            ->where('table_name', $tableName)
            ->where('column_name', 'id')
            ->exists();

        
        $query = DB::connection($connection)->table($qualifiedTable);
        // return DB::table($qualifiedTable)
        //     ->when($hasIdColumn, function ($q) {
        //         return $q->orderBy('id', 'desc');
        //     })
        //     ->limit(10)
        //     ->get()
        //     ->map(function ($item) {
        //         return (array) $item;
        //     })
        //     ->toArray();
        if ($hasIdColumn) {
            $query->orderBy('id', 'desc');
        }

        return $query
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return $this->normalizeRowForJson((array) $item);
            })
            ->toArray();
    }

    public function getOpenEndedResponses(ProjectData $projectData, $schemaName, $tableName): array
    {
       
        $schemaName = strtolower(trim((string) $schemaName));
        $tableName = strtolower(trim((string) $tableName));

        if ($schemaName === '' || preg_match('/[^a-z0-9_]/', $schemaName) === 1 || strlen($schemaName) > 63) {
            throw new InvalidArgumentException('Invalid schema name: ' . $schemaName);
        }

        if (strlen($tableName) > 55 || preg_match('/^[0-9]/', $tableName) === 1 || preg_match('/[^a-z0-9_]/', $tableName) === 1) {
            throw new InvalidArgumentException('Invalid table name: ' . $tableName);
        }

        $qualifiedTable = $schemaName . '.' . $tableName;

        $connection = DB::getDefaultConnection();
        if (DB::connection($connection)->getDriverName() !== 'pgsql') {
            throw new InvalidArgumentException('getOpenEndedResponses only supports pgsql; got ' . DB::connection($connection)->getDriverName());
        }

        if (!Schema::connection($connection)->hasTable($qualifiedTable)) {
            throw new InvalidArgumentException('Target table does not exist: ' . $qualifiedTable);
        }
        $openEndedDbColumns = $projectData->projectDataCsvs()
            ->whereHas('csvDataType', function ($q) {
                $q->where('csv_type_key', 'text-open-ended');
            })
            ->whereNotNull('db_column')
            ->pluck('db_column')
            ->map(function ($col) {
                return strtolower(trim((string) $col));
            })
            ->filter(function (string $col) {
                if ($col === '' || strlen($col) > 55) {
                    return false;
                }
                if (preg_match('/^[0-9]/', $col) === 1) {
                    return false;
                }
                return preg_match('/[^a-z0-9_]/', $col) !== 1;
            })
            ->unique()
            ->values()
            ->toArray();

        if (empty($openEndedDbColumns)) {
            return [];
        }

        $existingColumns = DB::connection($connection)
            ->table('information_schema.columns')
            ->where('table_schema', $schemaName)
            ->where('table_name', $tableName)
            ->whereIn('column_name', $openEndedDbColumns)
            ->pluck('column_name')
            ->map(function ($col) {
                return strtolower(trim((string) $col));
            })
            ->values()
            ->toArray();

        if (empty($existingColumns)) {
            return [];
        }

        return DB::connection($connection)
            ->table($qualifiedTable)
            ->select($existingColumns)
            ->get()
            ->map(function ($item) {
                return $this->normalizeRowForJson((array) $item);
            })
            ->toArray();
    }
    public function getOpenEndedResponsesForIncrementalAnalysis(ProjectData $projectData, $schemaName, $tableName): array
    {
        $schemaName = strtolower(trim((string) $schemaName));
        $tableName = strtolower(trim((string) $tableName));

        if ($schemaName === '' || preg_match('/[^a-z0-9_]/', $schemaName) === 1 || strlen($schemaName) > 63) {
            throw new InvalidArgumentException('Invalid schema name: ' . $schemaName);
        }

        if (strlen($tableName) > 55 || preg_match('/^[0-9]/', $tableName) === 1 || preg_match('/[^a-z0-9_]/', $tableName) === 1) {
            throw new InvalidArgumentException('Invalid table name: ' . $tableName);
        }

        $qualifiedTable = $schemaName . '.' . $tableName;

        $connection = DB::getDefaultConnection();
        if (DB::connection($connection)->getDriverName() !== 'pgsql') {
            throw new InvalidArgumentException('getOpenEndedResponsesForIncrementalAnalysis only supports pgsql; got ' . DB::connection($connection)->getDriverName());
        }

        if (!Schema::connection($connection)->hasTable($qualifiedTable)) {
            throw new InvalidArgumentException('Target table does not exist: ' . $qualifiedTable);
        }

        $openEndedDbColumnToCsvHeader = $projectData->projectDataCsvs()
            ->whereHas('csvDataType', function ($q) {
                $q->where('csv_type_key', 'text-open-ended');
            })
            ->whereNotNull('db_column')
            ->get(['db_column', 'csv_header'])
            ->map(function ($row) {
                return [
                    'db_column' => strtolower(trim((string) ($row->db_column ?? ''))),
                    'csv_header' => trim((string) ($row->csv_header ?? '')),
                ];
            })
            ->filter(function (array $row) {
                $col = $row['db_column'] ?? '';
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
            })
            ->unique('db_column')
            ->values()
            ->mapWithKeys(function (array $row) {
                $dbColumn = (string) ($row['db_column'] ?? '');
                $csvHeader = trim((string) ($row['csv_header'] ?? ''));

                return [$dbColumn => $csvHeader !== '' ? $csvHeader : $dbColumn];
            })
            ->toArray();

        if (empty($openEndedDbColumnToCsvHeader)) {
            return [];
        }

        $existingColumns = DB::connection($connection)
            ->table('information_schema.columns')
            ->where('table_schema', $schemaName)
            ->where('table_name', $tableName)
            ->whereIn('column_name', array_keys($openEndedDbColumnToCsvHeader))
            ->pluck('column_name')
            ->map(function ($col) {
                return strtolower(trim((string) $col));
            })
            ->values()
            ->toArray();

        if (empty($existingColumns)) {
            return [];
        }

        $csvHeaderByDbColumn = [];
        foreach ($existingColumns as $dbColumn) {
            $csvHeaderByDbColumn[$dbColumn] = $openEndedDbColumnToCsvHeader[$dbColumn] ?? $dbColumn;
        }

        return DB::connection($connection)
            ->table($qualifiedTable)
            ->select($existingColumns)
            ->get()
            ->map(function ($item) use ($csvHeaderByDbColumn) {
                $row = $this->normalizeRowForJson((array) $item);

                $renamed = [];
                foreach ($csvHeaderByDbColumn as $dbColumn => $csvHeader) {
                    $renamed[$csvHeader] = $row[$dbColumn] ?? null;
                }

                return $renamed;
            })
            ->toArray();
    }
    public function getRecordsFromOpenEndedColumns(Project $project): array
    {
        $openEndedResponsesTable = [];
        $openEndedResponses = [];
        $tables=$this->getAllTablesForProject($project);
        foreach($tables as $table){
            $openEndedResponsesTable['table_name'] = $table['csv_data_type_table_name'];
            $openEndedResponsesTable['responses'] = $this->getOpenEndedResponsesForIncrementalAnalysis($table, $project->schema_name, $table['csv_data_type_table_name']);
            $openEndedResponses[] = $openEndedResponsesTable;
            // Process the open-ended responses as needed
        }
        
        return $this->chunkOpenEndedResponses($openEndedResponses);
    }
    public function chunkOpenEndedResponses(array $openEndedResponses, int $chunkSize = 20): array
    {
        $chunkSize = max(1, (int) $chunkSize);

        $result = [];
        foreach ($openEndedResponses as $tableEntry) {
            if (!is_array($tableEntry)) {
                continue;
            }

            $tableName = (string) ($tableEntry['table_name'] ?? '');
            $responses = $tableEntry['responses'] ?? [];
            // return $array_chunked = collect($responses)->chunk($chunkSize)->toArray();
            if (!is_array($responses)) {
                $responses = [];
            }

            $result[] = [
                'table_name' => $tableName,
                'response_chunks' => array_values(array_map(function (array $chunk) {
                    return array_values($chunk);
                }, array_chunk(array_values($responses), $chunkSize))),
            ];
        }

        return $result;
    }
    public function getAllTablesForProject(Project $project)
    {
        return $project->tables;
    }
    
}