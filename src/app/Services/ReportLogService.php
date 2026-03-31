<?php

namespace App\Services;


class ReportLogService
{

    public function storeReportLogs(int $reportId, $agent, $message = null): void
    {
        \DB::table('report_logs')
            ->updateOrInsert(
                ['report_id' => $reportId, 'agent' => $agent],
                ['response' => null, 'error' => null, 'created_at' => now(), 'updated_at' => now(), 'display_message' => $message ?? 'Qualitative data insights for csv open-ended data completed.']
            );
    }
}
