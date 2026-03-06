<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Report;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class QdaOpenResponsesIncremental implements ShouldQueue
{
    use Batchable, Queueable;

    protected int $projectId;
    protected int $reportId;
    protected array $chunkData;
    protected ?string $modelKey;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project, array $chunkData, Report $report, ?string $modelKey = null)
    {
        $this->projectId = (int) $project->id;
        $this->reportId = (int) $report->id;
        $this->chunkData = $chunkData;
        $this->modelKey = $modelKey;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
    }
}
