<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Bus\Batchable;
class ManualModeMetricsDiscoveryJ implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */

    protected $user;
    protected $analysisPlanString;
    protected $jsonMetricData;
    protected $report;
    protected $project;

    public function __construct(User $user, $analysisPlanString, $jsonMetricData, $report, $project)
    {
        $this->user = $user;
        $this->analysisPlanString = $analysisPlanString;
        $this->jsonMetricData = $jsonMetricData;
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
