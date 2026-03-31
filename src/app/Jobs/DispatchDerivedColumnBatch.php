<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\QdaService;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use App\Services\ReportLogService;
use App\Events\ReportStatusUpdate;

class DispatchDerivedColumnBatch implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $projectId,
        protected ?int $userId = null,
        protected ?string $modelKey = null,
        protected ?int $reportId = null,
        protected ?string $prompt = null,
        protected mixed $qualitativeDataRaw = null,
    ) {}

    private function dispatchFollowUpJobsWithContext(): void
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

    public function handle(QdaService $qdaService): void
    {
        $reportLogService = new ReportLogService();
        $project = Project::find($this->projectId);
        $projectId = $this->projectId;
        $reportId = $this->reportId;
        $userId = $this->userId;
        $modelKey = $this->modelKey;
        $prompt = $this->prompt;
        $qualitativeDataRaw = $this->qualitativeDataRaw;
        if (!$project) {
            Log::warning('Skipping derived column batch because the project no longer exists.', [
                'project_id' => $this->projectId,
            ]);

            return;
        }

        $jobs = $qdaService->createDerivedColumnJobs($project, $this->modelKey, $this->userId);

        if ($jobs === []) {
            // Log::info('Skipping derived column batch because no jobs were generated.', [
            //     'project_id' => $this->projectId,
            // ]);
            $reportLogService->storeReportLogs($reportId, 'DerivedColumnBatch', 'Started deriving insights from open-ended responses.');
            event(new ReportStatusUpdate(reportId: $reportId));

            $this->dispatchFollowUpJobsWithContext();

            return;
        }

        

        $batch = Bus::batch($jobs)
            ->name('derived-columns-project-' . $this->projectId)
            ->allowFailures()
            ->finally(function (Batch $batch) use ($projectId, $reportId, $userId, $modelKey, $prompt, $qualitativeDataRaw, $reportLogService): void {
                // Log::info('Derived column batch finished.', [
                //     'project_id' => $projectId,
                //     'batch_id' => $batch->id,
                //     'report_id' => $reportId,
                // ]);

                $reportLogService->storeReportLogs($reportId, 'DerivedColumnBatch', 'Finished deriving insights from open-ended responses.');
                event(new ReportStatusUpdate(reportId: $reportId));
                if ($reportId === null) {
                    return;
                }

                $followUpJobs = [new ExecuteDerivedMetrics($reportId)];

                if ($prompt !== null) {
                    $followUpJobs[] = new CreateDashboardJ(
                        $userId,
                        $prompt,
                        $reportId,
                        $projectId,
                        $modelKey,
                        $qualitativeDataRaw,
                    );
                }

                Bus::chain($followUpJobs)->dispatch();
            })
            ->dispatch();

        // Log::info('Derived column batch dispatched.', [
        //     'project_id' => $this->projectId,
        //     'batch_id' => $batch->id,
        //     'job_count' => count($jobs),
        // ]);
        $reportLogService->storeReportLogs($reportId, 'DerivedColumnBatch', 'Started extracting insights from open-ended responses.');
        event(new ReportStatusUpdate(reportId: $reportId));
    }
}
