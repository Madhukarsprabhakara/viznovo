<?php

namespace App\Services;

use App\Models\CsvDataType;
use App\Models\ProjectDataCsv;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ProjectData;
use InvalidArgumentException;
use App\Models\Project;
use App\Jobs\CreateDerivedTable;
use App\Jobs\AddRecordsDerivedTable;

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
    public function createDJobs(Project $project)
    {
            $projectDataList = $project->derivedTables()->get();
            // return $projectDataList;
            $jobs = [];
            foreach ($projectDataList as $projectData) {
                $jobs[] = new CreateDerivedTable($project->schema_name, $projectData);
                $jobs[] = new AddRecordsDerivedTable($project->schema_name, $projectData);
            }
    
            return $jobs;

    }
}