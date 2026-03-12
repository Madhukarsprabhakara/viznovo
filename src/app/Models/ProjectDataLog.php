<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDataLog extends Model
{
    //
    protected $guarded = [];

    public function projectData()
    {
        return $this->belongsTo(ProjectData::class);
    }
}
