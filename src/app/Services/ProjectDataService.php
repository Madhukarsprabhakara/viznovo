<?php

namespace App\Services;
use App\Models\ProjectData;

class ProjectDataService
{
    public function buildDerivedTableNameFromSource(?string $sourceTableName, int $projectDataId): string
    {
        $sourceTableName = strtolower(trim((string) $sourceTableName));
        $sourceTableName = preg_replace('/[^a-z0-9_]/', '_', $sourceTableName) ?? '';
        $sourceTableName = preg_replace('/_+/', '_', $sourceTableName) ?? '';
        $sourceTableName = trim($sourceTableName, '_');
        $sourceTableName = preg_replace('/_(data_type|text)_\d+$/', '', $sourceTableName) ?? $sourceTableName;
        $sourceTableName = preg_replace('/_project_data_id_\d+_d$/', '', $sourceTableName) ?? $sourceTableName;
        $sourceTableName = rtrim($sourceTableName, '_');

        if ($sourceTableName === '') {
            $sourceTableName = 'derived';
        }

        return $this->normalizeDerivedTableName($sourceTableName, $projectDataId);
    }

    private function normalizeDerivedTableName(string $tableName, int $projectDataId): string
    {
        $tableName = strtolower(trim($tableName));
        $tableName = preg_replace('/[^a-z0-9_]/', '_', $tableName) ?? '';
        $tableName = preg_replace('/_+/', '_', $tableName) ?? '';
        $tableName = trim($tableName, '_');

        $suffix = '_project_data_id_' . $projectDataId . '_d';

        if (!str_ends_with($tableName, $suffix)) {
            $tableName = preg_replace('/_project_data_id_\d+_d$/', '', $tableName) ?? $tableName;
            $tableName = rtrim($tableName, '_');
        }

        $maxBaseLength = 55 - strlen($suffix);
        $base = substr($tableName, 0, max(1, $maxBaseLength));
        $base = rtrim($base, '_');

        if ($base === '') {
            $base = 't';
        }

        if (preg_match('/^[0-9]/', $base) === 1) {
            $base = 't_' . $base;
            $base = substr($base, 0, max(1, $maxBaseLength));
            $base = rtrim($base, '_');
        }

        if ($base === '') {
            $base = 't';
        }

        return $base . $suffix;
    }

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

            $projectData = ProjectData::find($projectDataId);
            if (!$projectData) {
                continue;
            }

            $normalizedTableName = $projectData->csv_derived_table_name
                ? (string) $projectData->csv_derived_table_name
                : $this->buildDerivedTableNameFromSource($projectData->csv_data_type_table_name, $projectDataId);

            $projectData->update([
                'csv_derived_table_name' => $normalizedTableName,
                'derived_json_schema' => json_encode($derivedTable) ?? null,

            ]);
            $tableNames[] = $normalizedTableName;
        }

        return $tableNames;
    }
    
}