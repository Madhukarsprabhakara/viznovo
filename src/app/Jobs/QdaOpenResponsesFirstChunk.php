<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Report;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Ai\Agents\ManualModeQualitativeCsvDataInsights;
use App\Services\JsonDataService;

class QdaOpenResponsesFirstChunk implements ShouldQueue
{
    use Batchable, Queueable;

    protected  $project;
    protected  $report;
    protected array $chunkData;
    protected ?string $modelKey;
    protected $user;
    protected string $tableName;
    protected int $chunkIndex;
    protected ?int $totalChunkCount;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project, $table_name, $chunk_index,array $chunkData, Report $report, ?string $modelKey = null, $user = null, ?int $totalChunkCount = null)
    {
        $this->project =  $project;
        $this->report =  $report;
        $this->tableName = $table_name;
        $this->chunkIndex = $chunk_index;
        $this->chunkData = $chunkData;
        $this->modelKey = $modelKey;
        $this->user = $user;
        $this->totalChunkCount = $totalChunkCount;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // dd('Processing first chunk for project ID: ' . $this->projectId . ', report ID: ' . $this->reportId, $this->chunkData);
        $jsonDataService = new JsonDataService();

        $prompt = "Here is the first chunk of qualitative data (table: {$this->tableName}, chunk_index: {$this->chunkIndex})...\n\n".json_encode($this->chunkData);
        if ($this->modelKey == 'gpt-5') { 
            $qdaInsights = (new ManualModeQualitativeCsvDataInsights)->forUser($this->user)
            ->prompt(
                $prompt,
                provider: [
                    'openai' => 'gpt-5.2',
                    'gemini' => 'gemini-3.1-pro-preview',
                ],
                timeout: 600,
            );
        } else {
            $qdaInsights = (new ManualModeQualitativeCsvDataInsights)->forUser($this->user)
            ->prompt(
                $prompt,
                provider: [
                    'gemini' => 'gemini-3.1-pro-preview',
                    'openai' => 'gpt-5.2',
                    
                ],
                timeout: 600,
            );

        }
        

        $qdaInsightsString = (string) $qdaInsights;
        [$qdaInsightsDecoded, $decodeError] = $jsonDataService->decodeAiJson($qdaInsightsString);

        if ($qdaInsightsDecoded) {
            // log the status

            \DB::table('report_log_open_endeds')
                ->updateOrInsert(
                    [
                        'report_id' => $this->report->id,
                        'table_name' => $this->tableName,
                        'chunk_index' => $this->chunkIndex,
                        'agent' => 'ManualModeQualitativeCsvDataInsights',
                    ],
                    ['response' => json_encode($qdaInsightsDecoded), 'error' => null, 'created_at' => now(), 'updated_at' => now(), 'table_name' => $this->tableName, 'chunk_index' => $this->chunkIndex, 'total_chunks' => $this->totalChunkCount]
                );
            
        } else {
            \DB::table('report_log_open_endeds')
                ->updateOrInsert(
                    [
                        'report_id' => $this->report->id,
                        'table_name' => $this->tableName,
                        'chunk_index' => $this->chunkIndex,
                        'agent' => 'ManualModeQualitativeCsvDataInsights',
                    ],
                    ['response' => null, 'error' => $decodeError ?: 'No insights found for the report.', 'created_at' => now(), 'updated_at' => now(), 'table_name' => $this->tableName, 'chunk_index' => $this->chunkIndex, 'total_chunks' => $this->totalChunkCount]
                );
        }

    }
}
