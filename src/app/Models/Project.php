<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Jobs\CreateSchemaOnProject;
use App\Jobs\DeleteSchemaOnProject;

class Project extends Model
{
    protected static function booted(): void
    {
        static::created(function (Project $project) {
            $schemaName = self::makeSchemaName($project->name, (int) $project->id);

            if (empty($project->schema_name)) {
                $project->schema_name = $schemaName;
                $project->saveQuietly();
            }

            CreateSchemaOnProject::dispatch(
                projectId: (int) $project->id,
                schemaName: $schemaName,
            );
        });

        static::deleting(function (Project $project) {
            $schemaName = $project->schema_name ?: self::makeSchemaName($project->name, (int) $project->id);

            DeleteSchemaOnProject::dispatch(
                projectId: (int) $project->id,
                schemaName: $schemaName,
            );
        });
    }

    public static function makeSchemaName(string $projectName, int $projectId): string
    {
        $base = strtolower($projectName);
        $base = preg_replace('/\s+/', '_', $base);
        $base = preg_replace('/[^a-z0-9_]/', '', $base);
        $base = preg_replace('/_+/', '_', $base);
        $base = trim($base, '_');

        if ($base === '' || $base === null) {
            $base = 'project';
        }

        $suffix = '_' . $projectId;
        $maxIdentifierLength = 63;
        $maxBaseLength = $maxIdentifierLength - strlen($suffix);
        if ($maxBaseLength < 1) {
            $base = 'project';
        } elseif (strlen($base) > $maxBaseLength) {
            $base = substr($base, 0, $maxBaseLength);
            $base = rtrim($base, '_');
            if ($base === '') {
                $base = 'project';
            }
        }

        return $base . $suffix;
    }

    public function files()
    {
        return $this->hasMany(ProjectData::class);
    }
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
