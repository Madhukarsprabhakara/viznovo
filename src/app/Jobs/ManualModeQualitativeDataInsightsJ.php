<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Bus\Batchable;
class ManualModeQualitativeDataInsightsJ implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */

    protected $user;
    protected $jsonQda;
    protected $report;
    protected $project;

    public function __construct(User $user, $jsonQda, $report, $project)
    {
        $this->user = $user;
        $this->jsonQda = $jsonQda;
        $this->report = $report;
        $this->project = $project;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
