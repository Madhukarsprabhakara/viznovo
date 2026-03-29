<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\DerivedTableService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class DispatchDerivedTableJobs implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $projectId,
        protected ?int $userId = null,
        protected ?string $modelKey = null,
        protected ?int $reportId = null,
        protected ?string $prompt = null,
        protected mixed $qualitativeDataRaw = null,
    ) {
    }

    private function dispatchFollowUpJobs(): void
    {
        if ($this->reportId === null) {
            return;
        }

        $jobs = [new ExecuteDerivedMetrics($this->reportId)];

        if ($this->prompt !== null) {
            $jobs[] = new CreateDashboardJ(
                $this->userId,
                $this->prompt,
                $this->reportId,
                $this->projectId,
                $this->modelKey,
                $this->qualitativeDataRaw,
            );
        }

        Bus::chain($jobs)->dispatch();
    }

    public function handle(DerivedTableService $derivedTableService): void
    {
        $project = Project::find($this->projectId);

        if (!$project) {
            Log::warning('Skipping derived table pipeline because the project no longer exists.', [
                'project_id' => $this->projectId,
            ]);

            return;
        }

        $jobs = $derivedTableService->createDJobs(
            $project,
            $this->modelKey,
            $this->userId,
            $this->reportId,
            $this->prompt,
            $this->qualitativeDataRaw,
        );

        if ($jobs === []) {
            Log::info('Skipping derived table pipeline because no derived tables were generated.', [
                'project_id' => $this->projectId,
            ]);

            $this->dispatchFollowUpJobs();

            return;
        }

        Log::info('Dispatching derived table pipeline.', [
            'project_id' => $this->projectId,
            'job_count' => count($jobs),
        ]);

        Bus::chain($jobs)->dispatch();
    }
}