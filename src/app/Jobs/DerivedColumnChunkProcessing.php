<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Ai\Agents\DerivedColumnChunkProcessor;
use App\Models\User;
use App\Services\JsonDataService;
use App\Services\DerivedTableService;
use App\Services\UserAiProviderConfigService;
use Illuminate\Support\Facades\Log;
// use App\Services\ReportLogService;
// use App\Events\ReportStatusUpdate;

class DerivedColumnChunkProcessing implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    protected array $chunk;
    protected string $previousCategories;
    protected string $schemaName;
    protected string $tableName;
    protected int $projectDataId;
    protected ?int $userId;
    protected ?string $modelKey;
    protected int $index;
    protected int $totalChunks;

    public function __construct(array $chunk, string $schemaName, string $tableName, int $projectDataId, ?int $userId, ?string $modelKey, int $index, int $totalChunks, string $previousCategories = '')
    {
        $this->chunk = $chunk;
        $this->schemaName = $schemaName;
        $this->tableName = $tableName;
        $this->projectDataId = $projectDataId;
        $this->userId = $userId;
        $this->modelKey = $modelKey;
        $this->index = $index;
        $this->totalChunks = $totalChunks;
        $this->previousCategories = $previousCategories;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        // $reportLogService = new ReportLogService();
        $jsonDataService = new JsonDataService();
        $derivedTableService = new DerivedTableService();
        $user = $this->userId ? User::find($this->userId) : null;

        app(UserAiProviderConfigService::class)->applyForUser($user?->id);

        $previousChunkString = $this->previousCategories ? 'Here are the previous categories identified from the previous chunks analysis that you can use for reference: ' . $this->previousCategories : '';
        if ($this->modelKey == 'gpt-5') {
            $response = (new DerivedColumnChunkProcessor)->forUser($user)
                ->prompt(
                    'Here are the instructions...\n\n' . $this->chunk['prompt'] . ' here are the 20 records in the chunk with chunk details:' . json_encode($this->chunk) . '\n\n' . $previousChunkString,
                    provider: [
                        'openai' => 'gpt-5.4',
                        'gemini' => 'gemini-3.1-pro-preview',
                        'ollama' => 'gemma4:e4b',
                    ],
                    timeout: 600,
                );
        }
        if ($this->modelKey == 'gemini-3-pro') {
            $response = (new DerivedColumnChunkProcessor)->forUser($user)
                ->prompt(
                    'Here are the instructions...\n\n' . $this->chunk['prompt'] . ' here are the 20 records in the chunk with chunk details:' . json_encode($this->chunk['records']) . '\n\n' . $previousChunkString,
                    provider: [
                        'gemini' => 'gemini-3.1-pro-preview',
                        'openai' => 'gpt-5.4',
                        'ollama' => 'gemma4:e4b',

                    ],
                    timeout: 600,
                );
        }
        if ($this->modelKey == 'gemma4:e4b') {
            $response = (new DerivedColumnChunkProcessor)->forUser($user)
                ->prompt(
                    'Here are the instructions...\n\n' . $this->chunk['prompt'] . ' here are the 20 records in the chunk with chunk details:' . json_encode($this->chunk['records']) . '\n\n' . $previousChunkString,
                    provider: [
                        'ollama' => 'gemma4:e4b',
                        'openai' => 'gpt-5.4',
                        'gemini' => 'gemini-3.1-pro-preview',
                    ],
                    timeout: 600,
                );
        }
        $rawResponseText = (string) $response;
        [$decoded, $decodeError] = $jsonDataService->decodeAiJson($rawResponseText);

        if ($decodeError !== null) {
            Log::warning('Derived column chunk response could not be decoded as JSON.', [
                'schema_name' => $this->schemaName,
                'table_name' => $this->tableName,
                'chunk_index' => $this->index,
                'total_chunks' => $this->totalChunks,
                'derived_db_column' => $this->chunk['derived_db_column'] ?? null,
                'error' => $decodeError,
                'raw_response' => $rawResponseText,
            ]);

            return;
        }

        $updated = $derivedTableService->storeDerivedData($decoded, $this->schemaName, $this->tableName, $this->chunk);


        Log::info('Derived column chunk processed.', [
            'updated_rows' => $updated,
            'decoded' => $decoded,
            'chunk' => $this->chunk,
        ]);
    }
}
