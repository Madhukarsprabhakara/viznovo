<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\ProjectDataMetric;

class ProjectDataMetricsService
{
    public function store(array $metricsArray, int $userId, ?int $reportId = null)
    {
        try {
            $metricWithResults = [];
            $now = now();

            // Accept either a single decoded payload with a top-level metrics key
            // or the existing array-of-payloads structure used elsewhere.
            if (array_key_exists('metrics', $metricsArray)) {
                $metricsArray = [$metricsArray];
            }

            if ($reportId !== null) {
                DB::table('project_data_metrics')->where('report_id', '=', $reportId)->delete();
            }

            foreach ($metricsArray as $metricData) {
                if (!is_array($metricData)) {
                    continue;
                }

                foreach (($metricData['metrics'] ?? []) as $metric) {
                    if (!is_array($metric)) {
                        continue;
                    }

                    $metricUserId = (int) ($metricData['user_id'] ?? $userId);
                    $metricProjectId = $metricData['project_id'] ?? null;

                    $sqlQuery = $metric['sql_query'] ?? '';
                    if (is_array($sqlQuery)) {
                        $sqlQuery = $sqlQuery[0] ?? '';
                    }
                    $sqlQuery = (string) $sqlQuery;

                    $row = [
                        'report_id' => $reportId,
                        'user_id' => $metricUserId,
                        'project_id' => $metricProjectId,
                        'metric_name' => $metric['metric_name'] ?? null,
                        'description' => $metric['description'] ?? null,
                        'sql_query' => $sqlQuery,
                        'result' => null,
                        'error' => null,
                        'is_successful' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $metricWithResults[] = $row;
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
    public function getDataForPromptDesign(int $reportId)
    {
        return ProjectDataMetric::where('report_id', $reportId)
            ->where('is_successful', true)
            ->get(['metric_name', 'description', 'result']);
    }
    public function checkMetricsExistForReport(int $reportId): bool
    {
        return ProjectDataMetric::where('report_id', $reportId)->exists();
    }
}
