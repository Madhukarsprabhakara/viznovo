<?php

namespace App\Services;
use League\Csv\Reader;
use League\Csv\Statement;

class CsvDataSourceService
{
    public function getDataFromCsvforDashboardCreate($file)
    {
       $filePath = storage_path('app/private/' . $file->url);

                    try {
                        // Create reader and assume first row is header
                        $csv = Reader::createFromPath($filePath, 'r');
                        $csv->setHeaderOffset(0);
                        $csv->setEscape('');

                        $stmt = new Statement()
                            ->limit(1000);

                        $records = $stmt->process($csv);
                        // return response()->json($records);
                        // Convert records iterator to array of associative arrays
                        // return $records = iterator_to_array($csv->getRecords(), false);
                    } catch (\League\Csv\Exception $e) {
                        // Fallback: try a simple parse if the CSV has no header or parsing fails
                        try {
                            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            $records = array_map(function ($line) {
                                return str_getcsv($line);
                            }, $lines);
                        } catch (\Exception $e2) {
                            $records = ['error' => 'Could not parse CSV: ' . $e2->getMessage()];
                        }
                    } catch (\Exception $e) {
                        $records = ['error' => 'Could not read CSV: ' . $e->getMessage()];
                    }

                    return [
                        'csv_filename' => $file->name ?? basename($file->system_name),
                        'csv_data' => $records,
                    ];
    }
    
    
}