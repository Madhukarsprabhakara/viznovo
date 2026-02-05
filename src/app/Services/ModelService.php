<?php

namespace App\Services;
use App\Models\AIModel;

class ModelService
{
    public function getModels()
    {
        // Logic to get available models
        return [
            ['id' => 'gpt-3.5-turbo', 'name' => 'GPT-3.5 Turbo'],
            ['id' => 'gpt-4', 'name' => 'GPT-4'],
            // Add more models as needed
        ];
    }
}