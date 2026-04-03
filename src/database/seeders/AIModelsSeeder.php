<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AIModel;
use Carbon\Carbon;
class AIModelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        AIModel::updateOrInsert(
            ['key' => 'gpt-5'],
            [
                'name' => 'GPT-5',
                'description' => 'OpenAI GPT-5.4',
                'from_company' => 'OpenAI',
                'key' => 'gpt-5',
                'context_window' => '1M Tokens',
                'is_admin_only' => false,
                'is_paid' => true,
                'sort_order' => 1,
                'is_active' => true,
                'is_default' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
         AIModel::updateOrInsert(
            ['key' => 'gemini-3-pro'],
            [
                'name' => 'Gemini 3',
                'description' => '"Google Gemini 3 Pro"',
                'from_company' => 'Google',
                'key' => 'gemini-3-pro',
                'context_window' => '1M Tokens',
                'is_admin_only' => false,
                'is_paid' => true,
                'sort_order' => 2,
                'is_active' => true,
                'is_default' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
