<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\ProjectDataMetric;

class ProjectDataMetricsService
{
    public function store(array $metricsArray, int $userId)
    {
        try {
            $metricWithResults = [];
            $now = now();
            foreach ($metricsArray as $metricData) {
                DB::table('project_data_metrics')->where('project_data_id', '=', $metricData['project_data_id'])->delete();
                foreach (($metricData['metrics'] ?? []) as $metric) {
                    if (!is_array($metric)) {
                        continue;
                    }
                   
                    $metric['user_id'] = $metricData['user_id'] ?? $userId;
                    $metric['project_id'] = $metricData['project_id'];
                    $metric['project_data_id'] = $metricData['project_data_id'];

                    $sqlQuery = $metric['sql_query'] ?? '';
                    if (is_array($sqlQuery)) {
                        $sqlQuery = $sqlQuery[0] ?? '';
                    }
                    $sqlQuery = (string) $sqlQuery;

                    [$result, $error] = $this->executeSql($sqlQuery);
                    $metric['result'] = $result !== null ? json_encode($result) : null;
                    $metric['error'] = $error;
                    $metric['is_successful'] = $error === null;
                    $metric['created_at'] = $now;
                    $metric['updated_at'] = $now;
                    $metricWithResults[] = $metric;
                }
            }

            if (!empty($metricWithResults)) {
                
                foreach (array_chunk($metricWithResults, 500) as $chunk) {
                    DB::table('project_data_metrics')->insert($chunk);
                }
            }

            return $metricWithResults;
        } catch (\Exception $e) {
            // Handle exceptions, log errors, etc.
            return ['error' => $e->getMessage()];
        }
    }

    public function executeSql(string $sql)
    {
        try {
            // Assuming you have a database connection set up, you can use DB facade to execute the query
            $sql = trim($sql);
            $sql = rtrim($sql, ";\n\r\t ");
            if ($sql === '') {
                return [null, 'Empty SQL query'];
            }

            $result = DB::select($sql);
            return [$result, null];
        } catch (\Exception $e) {
            // Handle exceptions, log errors, etc.
            return [null, $e->getMessage()];
        }
        // Logic to execute the SQL query and return results
    }
    public function getDataForPromptDesign(array $projectDataIds)
    {
        return ProjectDataMetric::whereIn('project_data_id', $projectDataIds)->where('is_successful', true)->get(['metric_name', 'description', 'result']);
    }
}
