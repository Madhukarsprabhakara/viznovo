<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
    public function files()
    {
        return $this->hasMany(ProjectData::class);
    }
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
