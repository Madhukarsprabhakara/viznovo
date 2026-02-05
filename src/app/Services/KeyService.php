<?php

namespace App\Services;
use App\Models\ModelAccess;

class KeyService
{
    public function getModelAccess($modelKey, $userId)
    {
        return ModelAccess::where('model_key', $modelKey)
            ->where('user_id', $userId)
            ->first();
    }
    
}