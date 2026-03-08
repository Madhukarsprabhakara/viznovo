<?php

namespace App\Services;

use App\Jobs\QdaOpenResponsesFirstChunk;
use App\Jobs\QdaOpenResponsesIncremental;
use App\Models\Project;
use App\Models\Report;
class QdaService
{

    public function createJobs(Project $project, array $openEndedResponseChunks, Report $report, ?string $modelKey = null, $user = null)
    {
        $firstChunkJobs = [];
        $remainingChunkJobs = [];

        foreach ($openEndedResponseChunks as $tableEntry) {
            if (!is_array($tableEntry)) {
                continue;
            }

            $tableName = $tableEntry['table_name'] ?? null;
            $chunks = $tableEntry['response_chunks'] ?? null;
            $totalChunkCount = is_array($chunks) ? count($chunks) : null;
            if (!is_string($tableName) || $tableName === '') {
                continue;
            }

            if (!is_array($chunks) || $chunks === []) {
                continue;
            }

            $chunks = array_values($chunks);

            $firstChunk = $chunks[0] ?? null;
            if (is_array($firstChunk) && $firstChunk !== []) {
                $firstChunkJobs[] = new QdaOpenResponsesFirstChunk(
                    $project, 
                    $tableName, 
                    0, 
                    array_values($firstChunk), 
                    $report, 
                    $modelKey, 
                    $user,
                    $totalChunkCount,
               );
            }

            $chunkCount = count($chunks);
            $incrementalChain = [];
            for ($chunkIndex = 1; $chunkIndex < $chunkCount; $chunkIndex++) {
                $chunk = $chunks[$chunkIndex] ?? null;
                if (!is_array($chunk) || $chunk === []) {
                    continue;
                }

                $incrementalChain[] = new QdaOpenResponsesIncremental(
                    $project,
                    $tableName,
                    $chunkIndex,
                    array_values($chunk),
                    $report,
                    $modelKey,
                    $user,
                    $totalChunkCount,
                );
            }

            // if ($incrementalChain !== []) {
            //     // Nested arrays create per-table job chains inside a batch.
            //     $remainingChunkJobs[] = $incrementalChain;
            // }
        }

        return [
            'first_chunk_jobs' => $firstChunkJobs,
            'remaining_chunk_jobs' => $incrementalChain,
        ];
    }
    
    
}
