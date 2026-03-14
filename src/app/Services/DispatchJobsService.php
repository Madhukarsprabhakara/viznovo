<?php

namespace App\Services;


class DispatchJobsService
{
    public function decideAndDispatch(array $input_metric_data, array $qda, array $qdaJobs)
    {
        $truthValues = [
            'pgsqlTableExists' => count($input_metric_data['pgsql_tables'] ?? []) > 0,
            'pdfExists' => count($input_metric_data['pdf_content'] ?? []) > 0,
            'websiteContentExists' => count($input_metric_data['website_urls'] ?? []) > 0,
            'openEndedFirstChunkExists' => count($qdaJobs['first_chunk_jobs'] ?? []) > 0,
            'openEndedIncrementalExists' => count($qdaJobs['remaining_chunk_jobs'] ?? []) > 0,
        ];
        
        return $truthValues;


    }
    
}