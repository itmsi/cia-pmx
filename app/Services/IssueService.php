<?php

namespace App\Services;

use App\Models\IssueModel;
use App\Services\ActivityLogService;
use App\Services\ProjectService;

class IssueService
{
    protected IssueModel $issueModel;
    protected ActivityLogService $logService;
    protected ProjectService $projectService;
    protected $db;

    public function __construct()
    {
        $this->issueModel = new IssueModel();
        $this->logService = new ActivityLogService();
        $this->projectService = new ProjectService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new issue
     */
    public function createIssue(int $projectId, int $columnId, string $title, int $reporterId, array $options = []): int
    {
        // Generate issue key
        $issueKey = $this->projectService->generateIssueKey($projectId);

        $issueId = $this->issueModel->insert([
            'project_id' => $projectId,
            'issue_key' => $issueKey,
            'column_id' => $columnId,
            'issue_type' => $options['issue_type'] ?? 'task',
            'priority' => $options['priority'] ?? 'medium',
            'title' => $title,
            'description' => $options['description'] ?? null,
            'assignee_id' => $options['assignee_id'] ?? null,
            'reporter_id' => $reporterId,
            'due_date' => $options['due_date'] ?? null,
            'estimation' => $options['estimation'] ?? null,
            'parent_issue_id' => $options['parent_issue_id'] ?? null,
            'position' => $options['position'] ?? 0,
        ]);

        $this->logService->log(
            'create',
            'issue',
            $issueId,
            "Issue created: {$issueKey} - {$title}"
        );

        return $issueId;
    }

    /**
     * Get issues by project
     */
    public function getIssuesByProject(int $projectId): array
    {
        return $this->issueModel
            ->where('project_id', $projectId)
            ->orderBy('position', 'ASC')
            ->findAll();
    }

    /**
     * Get issues by column
     */
    public function getIssuesByColumn(int $columnId): array
    {
        return $this->issueModel
            ->where('column_id', $columnId)
            ->orderBy('position', 'ASC')
            ->findAll();
    }

    /**
     * Get issue by ID
     */
    public function getIssueById(int $issueId): ?array
    {
        return $this->issueModel->find($issueId);
    }

    /**
     * Get issue by key
     */
    public function getIssueByKey(string $issueKey): ?array
    {
        return $this->issueModel->where('issue_key', $issueKey)->first();
    }

    /**
     * Get sub-tasks of an issue
     */
    public function getSubTasks(int $issueId): array
    {
        return $this->issueModel
            ->where('parent_issue_id', $issueId)
            ->orderBy('position', 'ASC')
            ->findAll();
    }

    /**
     * Update issue
     */
    public function updateIssue(int $issueId, array $data): bool
    {
        $result = $this->issueModel->update($issueId, $data);

        if ($result) {
            $this->logService->log(
                'update',
                'issue',
                $issueId,
                'Issue updated'
            );
        }

        return $result;
    }

    /**
     * Move issue to different column (status change)
     */
    public function moveIssue(int $issueId, int $targetColumnId, int $position): bool
    {
        $issue = $this->getIssueById($issueId);
        if (!$issue) {
            return false;
        }

        // Log status change if column changed
        if ($issue['column_id'] != $targetColumnId) {
            $this->logService->log(
                'update',
                'issue',
                $issueId,
                "Issue moved from column {$issue['column_id']} to {$targetColumnId}"
            );
        }

        return $this->issueModel->update($issueId, [
            'column_id' => $targetColumnId,
            'position' => $position
        ]);
    }

    /**
     * Assign issue to user
     */
    public function assignIssue(int $issueId, ?int $assigneeId): bool
    {
        $result = $this->issueModel->update($issueId, [
            'assignee_id' => $assigneeId
        ]);

        if ($result) {
            $this->logService->log(
                'update',
                'issue',
                $issueId,
                $assigneeId ? "Issue assigned to user {$assigneeId}" : "Issue unassigned"
            );
        }

        return $result;
    }

    /**
     * Delete issue
     */
    public function deleteIssue(int $issueId): bool
    {
        // Check if has sub-tasks
        $subTasks = $this->getSubTasks($issueId);
        if (!empty($subTasks)) {
            throw new \RuntimeException('Cannot delete issue: has sub-tasks. Delete sub-tasks first.');
        }

        $result = $this->issueModel->delete($issueId);

        if ($result) {
            $this->logService->log(
                'delete',
                'issue',
                $issueId,
                'Issue deleted'
            );
        }

        return $result;
    }

    /**
     * Get issues assigned to user
     */
    public function getIssuesForUser(int $userId, ?int $projectId = null): array
    {
        $builder = $this->issueModel->where('assignee_id', $userId);

        if ($projectId) {
            $builder->where('project_id', $projectId);
        }

        return $builder->orderBy('due_date', 'ASC')
            ->orderBy('priority', 'DESC')
            ->findAll();
    }

    /**
     * Get overdue issues
     */
    public function getOverdueIssues(?int $projectId = null): array
    {
        $builder = $this->issueModel
            ->where('due_date <', date('Y-m-d'))
            ->where('column_id !=', function($builder) {
                // Assuming 'Done' column has a specific ID or name
                // This needs to be adjusted based on your column setup
                return $builder->select('id')
                    ->from('columns')
                    ->where('name', 'Done')
                    ->limit(1);
            });

        if ($projectId) {
            $builder->where('project_id', $projectId);
        }

        return $builder->orderBy('due_date', 'ASC')->findAll();
    }

    /**
     * Reorder issues in column
     */
    public function reorderIssues(int $columnId, array $issueIds): bool
    {
        $this->db->transStart();

        foreach ($issueIds as $position => $issueId) {
            $this->issueModel->update($issueId, [
                'column_id' => $columnId,
                'position' => $position
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}

