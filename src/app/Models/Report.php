<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    //
    protected $guarded = [];

    protected $appends = ['time_taken_minutes'];

    public function getTimeTakenMinutesAttribute(): ?float
    {
        $startEpoch = $this->start_epoch;
        $endEpoch = $this->end_epoch;

        if (! is_null($startEpoch) && ! is_null($endEpoch)) {
            $seconds = max(0, (int) $endEpoch - (int) $startEpoch);

            return round($seconds / 60, 2);
        }

        if (is_null($this->time_taken_seconds)) {
            return null;
        }

        return round(((float) $this->time_taken_seconds) / 60, 2);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function reportLogs()
    {
        return $this->hasMany(ReportLog::class)->orderBy('created_at', 'asc');
    }
    public function metrics()
    {
        return $this->hasMany(ProjectDataMetric::class, 'report_id');
    }
}
