<?php

namespace App\Services;

use App\Models\LogModel;

class LogService
{
    public function __construct(private LogModel $logs)
    {
    }

    public function add(
        ?int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $message = null,
        array $meta = []
    ): void {
        $this->logs->insert([
            'user_id'     => $userId ?: null,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'message'     => $message,
            'meta'        => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
