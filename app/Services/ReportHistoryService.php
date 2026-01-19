<?php

namespace App\Services;

use App\Models\ReportVersionModel;

class ReportHistoryService
{
    public function __construct(private readonly ReportVersionModel $history)
    {
    }

    public function add(
        int $reportId,
        string $changeType,
        ?int $changedBy,
        ?string $comment = null,
        ?string $versionLabel = null
    ): void {
        $this->history->insert([
            'report_id'      => $reportId,
            'change_type'    => $changeType,
            'changed_by'     => $changedBy,
            'comment'        => $comment,
            'version_label'  => $versionLabel,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}
