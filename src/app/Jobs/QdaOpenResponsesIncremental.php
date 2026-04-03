<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Report;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Ai\Agents\ManualModeQualitativeCsvDataInsightsIncrement;
use App\Services\JsonDataService;
use App\Services\UserAiProviderConfigService;
use App\Events\ReportStatusUpdate;
use Illuminate\Support\Facades\DB;
class QdaOpenResponsesIncremental implements ShouldQueue
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
        app(UserAiProviderConfigService::class)->applyForUser($this->user?->id);
        
        $jsonDataService = new JsonDataService();

        $previousChunkIndex = $this->chunkIndex - 1;
        $previousInsightsJson = null;
        if ($previousChunkIndex >= 0) {
            $previousInsightsJson = DB::table('report_log_open_endeds')
                ->where('report_id', $this->report->id)
                ->where('table_name', $this->tableName)
                ->where('chunk_index', $previousChunkIndex)
                ->whereNotNull('response')
                ->orderByDesc('updated_at')
                ->value('response');
        }

        $previousInsightsJson = is_string($previousInsightsJson) && $previousInsightsJson !== ''
            ? $previousInsightsJson
            : '{"qualitative_insights":{"open_ended_responses":[]}}';

        $prompt = "Here are the insights JSON from the previous chunk (table: {$this->tableName}, chunk_index: {$previousChunkIndex}). "
            ."Use these as the existing insights and incrementally add any NEW insights from the next chunk.\n\n"
            ."PREVIOUS_INSIGHTS_JSON:\n{$previousInsightsJson}\n\n"
            ."NEW_QUALITATIVE_DATA_CHUNK (chunk_index: {$this->chunkIndex}):\n".json_encode($this->chunkData);
        if ($this->modelKey == 'gpt-5') { 
            $qdaInsights = (new ManualModeQualitativeCsvDataInsightsIncrement)->forUser($this->user)
            ->prompt(
                $prompt,
                provider: [
                    'openai' => 'gpt-5.2',
                    'gemini' => 'gemini-3.1-pro-preview',
                ],
                timeout: 600,
            );
        } else {
            $qdaInsights = (new ManualModeQualitativeCsvDataInsightsIncrement)->forUser($this->user)
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

            DB::table('report_log_open_endeds')
                ->updateOrInsert(
                    [
                        'report_id' => $this->report->id,
                        'table_name' => $this->tableName,
                        'chunk_index' => $this->chunkIndex,
                        'agent' => 'ManualModeQualitativeCsvDataInsightsIncrement',
                    ],
                    ['response' => json_encode($qdaInsightsDecoded), 'error' => null, 'created_at' => now(), 'updated_at' => now(), 'table_name' => $this->tableName, 'chunk_index' => $this->chunkIndex, 'total_chunks' => $this->totalChunkCount]
                );
                if ($this->chunkIndex+1 === $this->totalChunkCount) {
                    event(new ReportStatusUpdate(reportId: $this->report->id));
                    DB::table('report_logs')
                        ->updateOrInsert(
                            ['report_id' => $this->report->id, 'agent' => 'ManualModeQualitativeCsvDataInsightsIncrement'],
                            ['response' => json_encode($qdaInsightsDecoded), 'error' => null, 'created_at' => now(), 'updated_at' => now(), 'display_message' => 'Qualitative data insights for csv open-ended data completed.']
                        );
                } 
            
        } else {
            DB::table('report_log_open_endeds')
                ->updateOrInsert(
                    [
                        'report_id' => $this->report->id,
                        'table_name' => $this->tableName,
                        'chunk_index' => $this->chunkIndex,
                        'agent' => 'ManualModeQualitativeCsvDataInsightsIncrement',
                    ],
                    ['response' => null, 'error' => $decodeError ?: 'No insights found for the report.', 'created_at' => now(), 'updated_at' => now(), 'table_name' => $this->tableName, 'chunk_index' => $this->chunkIndex, 'total_chunks' => $this->totalChunkCount]
                );

                DB::table('report_logs')
                        ->updateOrInsert(
                            ['report_id' => $this->report->id, 'agent' => 'ManualModeQualitativeCsvDataInsightsIncrement'],
                            ['response' => json_encode($qdaInsightsDecoded), 'error' => null, 'created_at' => now(), 'updated_at' => now(), 'display_message' => 'Something went wrong with incremental insights generation.']
                        );

        }

    }
}
