<?php

namespace App\Services;

use App\Jobs\QdaOpenResponsesFirstChunk;
use App\Jobs\QdaOpenResponsesIncremental;
use App\Models\Project;
use App\Models\Report;
class QdaService
{

    public function createJobs(Project $project, array $openEndedResponseChunks, Report $report)
    {
        $firstChunkJobs = [];
        $remainingChunkJobs = [];

        foreach ($openEndedResponseChunks as $tableEntry) {
            if (!is_array($tableEntry)) {
                continue;
            }

            $tableName = $tableEntry['table_name'] ?? null;
            $chunks = $tableEntry['response_chunks'] ?? null;

            if (!is_string($tableName) || $tableName === '') {
                continue;
            }

            if (!is_array($chunks) || $chunks === []) {
                continue;
            }

            $chunks = array_values($chunks);

            $firstChunk = $chunks[0] ?? null;
            if (is_array($firstChunk) && $firstChunk !== []) {
                $firstChunkJobs[] = new QdaOpenResponsesFirstChunk($project, [
                    'project_id' => $project->id,
                    'table_name' => $tableName,
                    'chunk_index' => 0,
                    'responses' => array_values($firstChunk),
                ], $report);
            }

            $chunkCount = count($chunks);
            for ($chunkIndex = 1; $chunkIndex < $chunkCount; $chunkIndex++) {
                $chunk = $chunks[$chunkIndex] ?? null;
                if (!is_array($chunk) || $chunk === []) {
                    continue;
                }

                $remainingChunkJobs[] = new QdaOpenResponsesIncremental($project, [
                    'project_id' => $project->id,
                    'table_name' => $tableName,
                    'chunk_index' => $chunkIndex,
                    'responses' => array_values($chunk),
                ], $report);
            }
        }

        return [
            'first_chunk_jobs' => $firstChunkJobs,
            'remaining_chunk_jobs' => $remainingChunkJobs,
        ];
    }
    
    
}
