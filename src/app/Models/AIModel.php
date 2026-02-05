<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIModel extends Model
{
    //
    protected $guarded = [];

    public function getModels()
    {
        return AIModel::all();
    
    }
}
