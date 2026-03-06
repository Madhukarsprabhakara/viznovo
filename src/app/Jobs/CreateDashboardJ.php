<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Report;
class CreateDashboardJ implements ShouldQueue
{
    use Queueable;

    protected $report;
    protected $prompt;
    protected $project;
    protected $data_for_prompt_design;
    /**
     * Create a new job instance.
     */
    public function __construct($prompt, Report $report, $project, $data_for_prompt_design = [])
    {
        $this->report = $report;
        $this->prompt = $prompt;
        $this->project = $project;
        $this->data_for_prompt_design = $data_for_prompt_design;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
