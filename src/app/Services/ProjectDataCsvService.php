<?php

namespace App\Services;

use App\Models\CsvDataType;
use App\Models\ProjectData;
use App\Models\ProjectDataCsv;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ProjectDataCsvService
{
    /**
     * @param array<int, array{original_csv_header:string, db_column:string}> $columns
     */
    public function storeCsvColumns(ProjectData $projectData, array $columns, string $tableType = 'text', ?int $userId = null)
    {
        // $csvDataTypeId = $this->getTextOpenEndedCsvDataTypeId($col['db_column']);

        $rows = [];
        $now = now();
        foreach ($columns as $col) {
            $csvHeader = (string) ($col['csv_header'] ?? '');
            $dbColumn = (string) ($col['db_column'] ?? $this->getDbColumnFromCsvHeader(trim($csvHeader), $projectData->id));
            $csvHeader = trim($csvHeader);
            $dbColumn = trim($dbColumn);
            

            $row = [
                'project_data_id' => (int) $projectData->id,
                'csv_data_type_id' => (int) $this->getTextOpenEndedCsvDataTypeId($col['data_type']),
                'user_id' => $projectData->user_id,
                'csv_header' => $csvHeader,
                'db_column' => $dbColumn,
                'created_at' => $now,
                'updated_at' => $now,
                'table_type' => $tableType,
            ];

            
            // return $projectData->id;
            $rows[] = $row;
        }
        // return $rows;
        $status=ProjectDataCsv::insert($rows);
        return $status;
        
    }

    private function getTextOpenEndedCsvDataTypeId(string $key): int
    {
        $type = CsvDataType::where('csv_type_key', $key)->first();
        if ($type) {
            return (int) $type->id;
        }

        // Create it if it doesn't exist yet.
        $created = CsvDataType::create([
            'csv_type_key' => $key,
            'db_type' => 'text',
            'laravel_type' => 'text',
        ]);

        if (!$created) {
            throw new InvalidArgumentException('Unable to resolve csv_data_types row for ' . $key);
        }

        return (int) $created->id;
    }
    public function getDbColumnFromCsvHeader(string $csvHeader, int $projectDataId): string
    {
       return ProjectDataCsv::where('project_data_id', $projectDataId)
            ->where('csv_header', $csvHeader)->where('table_type', 'text_table')
            ->first('db_column')->db_column ?? '';
    }
}