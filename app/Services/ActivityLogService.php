<?php

namespace App\Services;

use App\Models\ActivityLogModel;

class ActivityLogService
{
    protected ActivityLogModel $logModel;

    public function __construct()
    {
        $this->logModel = new ActivityLogModel();
    }

    public function log(
        string $action,
        string $entityType,
        ?int $entityId,
        ?string $description = null
    ): void {
        $this->logModel->insert([
            'user_id'     => session()->get('user_id'),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'description' => $description,
        ]);
    }
}
