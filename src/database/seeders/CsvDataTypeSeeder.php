<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CsvDataType;
use Carbon\Carbon;

class CsvDataTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        CsvDataType::updateOrInsert(
            ['csv_type_key' => 'text-categorical'],
            [
                'csv_type_key' => 'text-categorical',
                'db_type' => 'TEXT',
                'laravel_type' => 'longText',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        CsvDataType::updateOrInsert(
            ['csv_type_key' => 'text-open-ended'],
            [
                'csv_type_key' => 'text-open-ended',
                'db_type' => 'TEXT',
                'laravel_type' => 'longText',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        CsvDataType::updateOrInsert(
            ['csv_type_key' => 'timestamp'],
            [
                'csv_type_key' => 'timestamp',
                'db_type' => 'TIMESTAMP',
                'laravel_type' => 'timestamp',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        CsvDataType::updateOrInsert(
            ['csv_type_key' => 'numeric'],
            [
                'csv_type_key' => 'numeric',
                'db_type' => 'DOUBLE',
                'laravel_type' => 'double',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        CsvDataType::updateOrInsert(
            ['csv_type_key' => 'date'],
            [
                'csv_type_key' => 'date',
                'db_type' => 'DATE',
                'laravel_type' => 'date',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
