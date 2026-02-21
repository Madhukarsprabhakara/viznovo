<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ProjectData extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (ProjectData $projectData) {
            $projectData->dropAssociatedCsvTables();
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    private function dropAssociatedCsvTables(): void
    {
        $connection = DB::getDefaultConnection();
        if (DB::connection($connection)->getDriverName() !== 'pgsql') {
            return;
        }

        $project = $this->project;
        if (!$project && $this->project_id) {
            $project = Project::find($this->project_id);
        }
        if (!$project) {
            return;
        }

        $schemaName = $project->schema_name ?: Project::makeSchemaName((string) $project->name, (int) $project->id);
        $schemaName = strtolower(trim((string) $schemaName));
        if ($schemaName === '' || preg_match('/\A[a-z][a-z0-9_]*\z/', $schemaName) !== 1 || strlen($schemaName) > 63) {
            return;
        }

        $schemaExists = DB::connection($connection)->selectOne(
            'select 1 from information_schema.schemata where schema_name = ?',
            [$schemaName]
        );
        if (!$schemaExists) {
            return;
        }

        foreach (['csv_text_table_name', 'csv_data_type_table_name'] as $field) {
            $tableName = strtolower(trim((string) ($this->{$field} ?? '')));
            if ($tableName === '' || preg_match('/\A[a-z_][a-z0-9_]*\z/', $tableName) !== 1 || strlen($tableName) > 63) {
                continue;
            }

            $tableExists = DB::connection($connection)->selectOne(
                'select 1 from information_schema.tables where table_schema = ? and table_name = ?',
                [$schemaName, $tableName]
            );
            if (!$tableExists) {
                continue;
            }

            DB::connection($connection)->statement('DROP TABLE IF EXISTS "' . $schemaName . '"."' . $tableName . '"');
        }
    }
}
