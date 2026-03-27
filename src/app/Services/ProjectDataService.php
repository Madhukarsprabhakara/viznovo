<?php

namespace App\Services;
use App\Models\ProjectData;

class ProjectDataService
{
    public function storeDerivedTableName(array $derivedTables): array
    {
        $tableNames = [];
        foreach ($derivedTables as $derivedTable) {
            if (!is_array($derivedTable)) {
                continue;
            }

            $projectDataId = isset($derivedTable['project_data_id']) ? (int) $derivedTable['project_data_id'] : null;
            if (!$projectDataId) {
                continue;
            }
            ProjectData::find($projectDataId)->update([
                'csv_derived_table_name' => trim((string) ($derivedTable['table_name'] ?? '')),
                'derived_json_schema' => json_encode($derivedTable) ?? null,

            ]);
            $tableNames[] = trim((string) ($derivedTable['table_name'] ?? ''));
        }

        return $tableNames;
    }
    
}