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

    /**
     * Log status change with detailed information
     */
    public function logStatusChange(
        int $issueId,
        int $fromColumnId,
        int $toColumnId,
        string $fromColumnName,
        string $toColumnName
    ): void {
        $description = "Status changed from '{$fromColumnName}' to '{$toColumnName}'";
        
        // Store additional metadata in description (can be enhanced with JSON field later)
        $metadata = [
            'from_column_id' => $fromColumnId,
            'to_column_id' => $toColumnId,
            'from_column_name' => $fromColumnName,
            'to_column_name' => $toColumnName
        ];
        
        $this->logModel->insert([
            'user_id'     => session()->get('user_id'),
            'action'      => 'status_change',
            'entity_type' => 'issue',
            'entity_id'   => $issueId,
            'description' => $description . ' | Metadata: ' . json_encode($metadata),
        ]);
    }

    /**
     * Get status change history for an issue
     */
    public function getStatusChangeHistory(int $issueId): array
    {
        return $this->logModel
            ->where('entity_type', 'issue')
            ->where('entity_id', $issueId)
            ->where('action', 'status_change')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
