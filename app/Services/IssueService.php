<?php

namespace App\Services;

use App\Models\IssueModel;
use App\Services\ActivityLogService;
use App\Services\ProjectService;
use App\Services\WorkflowService;

class IssueService
{
    protected IssueModel $issueModel;
    protected ActivityLogService $logService;
    protected ProjectService $projectService;
    protected WorkflowService $workflowService;
    protected $db;

    public function __construct()
    {
        $this->issueModel = new IssueModel();
        $this->logService = new ActivityLogService();
        $this->projectService = new ProjectService();
        $this->workflowService = new WorkflowService();
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
            throw new \RuntimeException('Issue not found');
        }

        $fromColumnId = $issue['column_id'];
        
        // Check if column changed
        if ($fromColumnId == $targetColumnId) {
            // Only update position if same column
            return $this->issueModel->update($issueId, [
                'position' => $position
            ]);
        }

        // Get board ID from issue's project
        $boardId = null;
        $board = $this->db->table('boards')
            ->where('project_id', $issue['project_id'])
            ->first();
        if ($board) {
            $boardId = $board->id;
        }

        // Validate workflow transition
        $validation = $this->workflowService->canTransition(
            $fromColumnId,
            $targetColumnId,
            $boardId,
            $issueId
        );

        if (!$validation['allowed']) {
            throw new \RuntimeException($validation['message'] ?? 'This transition is not allowed');
        }

        // Get column names for logging
        $fromColumn = $this->db->table('columns')->where('id', $fromColumnId)->get()->getRowArray();
        $toColumn = $this->db->table('columns')->where('id', $targetColumnId)->get()->getRowArray();
        $fromColumnName = $fromColumn['name'] ?? "Column {$fromColumnId}";
        $toColumnName = $toColumn['name'] ?? "Column {$targetColumnId}";

        // Update issue
        $result = $this->issueModel->update($issueId, [
            'column_id' => $targetColumnId,
            'position' => $position
        ]);

        if ($result) {
            // Log status change with detailed information
            $this->logService->logStatusChange(
                $issueId,
                $fromColumnId,
                $targetColumnId,
                $fromColumnName,
                $toColumnName
            );
        }

        return $result;
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

    /**
     * Get filtered issues
     * 
     * @param array $filters Filter parameters:
     *   - project_id: Filter by project
     *   - column_id: Filter by status/column (can be array)
     *   - priority: Filter by priority (can be array)
     *   - assignee_id: Filter by assignee (can be array, null for unassigned)
     *   - label_id: Filter by label (can be array)
     *   - issue_type: Filter by issue type (can be array)
     *   - due_date_from: Filter by due date from
     *   - due_date_to: Filter by due date to
     *   - due_date_overdue: Filter overdue issues (true/false)
     *   - search: Search in title and description
     * @param int|null $userId Optional user ID for access control
     * @return array
     */
    public function getFilteredIssues(array $filters = [], ?int $userId = null): array
    {
        $builder = $this->db->table('issues')
            ->select('issues.*, columns.name as column_name, users.full_name as assignee_name, users.email as assignee_email')
            ->join('columns', 'columns.id = issues.column_id', 'left')
            ->join('users', 'users.id = issues.assignee_id', 'left');

        // Filter by project
        if (!empty($filters['project_id'])) {
            $builder->where('issues.project_id', (int)$filters['project_id']);
        }

        // Filter by status/column
        if (!empty($filters['column_id'])) {
            if (is_array($filters['column_id'])) {
                $builder->whereIn('issues.column_id', $filters['column_id']);
            } else {
                $builder->where('issues.column_id', (int)$filters['column_id']);
            }
        }

        // Filter by priority
        if (!empty($filters['priority'])) {
            if (is_array($filters['priority'])) {
                $builder->whereIn('issues.priority', $filters['priority']);
            } else {
                $builder->where('issues.priority', $filters['priority']);
            }
        }

        // Filter by assignee
        if (isset($filters['assignee_id'])) {
            if (is_array($filters['assignee_id'])) {
                if (in_array(null, $filters['assignee_id'], true)) {
                    // Include unassigned
                    $builder->groupStart();
                    $builder->whereIn('issues.assignee_id', array_filter($filters['assignee_id']));
                    $builder->orWhere('issues.assignee_id IS NULL');
                    $builder->groupEnd();
                } else {
                    $builder->whereIn('issues.assignee_id', $filters['assignee_id']);
                }
            } elseif ($filters['assignee_id'] === null || $filters['assignee_id'] === 'null') {
                $builder->where('issues.assignee_id IS NULL');
            } else {
                $builder->where('issues.assignee_id', (int)$filters['assignee_id']);
            }
        }

        // Filter by label
        if (!empty($filters['label_id'])) {
            $labelIds = is_array($filters['label_id']) ? $filters['label_id'] : [(int)$filters['label_id']];
            $builder->join('issue_labels', 'issue_labels.issue_id = issues.id', 'inner')
                ->whereIn('issue_labels.label_id', $labelIds)
                ->groupBy('issues.id');
        }

        // Filter by issue type
        if (!empty($filters['issue_type'])) {
            if (is_array($filters['issue_type'])) {
                $builder->whereIn('issues.issue_type', $filters['issue_type']);
            } else {
                $builder->where('issues.issue_type', $filters['issue_type']);
            }
        }

        // Filter by due date range
        if (!empty($filters['due_date_from'])) {
            $builder->where('issues.due_date >=', $filters['due_date_from']);
        }
        if (!empty($filters['due_date_to'])) {
            $builder->where('issues.due_date <=', $filters['due_date_to']);
        }

        // Filter overdue issues
        if (!empty($filters['due_date_overdue'])) {
            $builder->where('issues.due_date <', date('Y-m-d'))
                ->where('issues.due_date IS NOT NULL', null, false);
        }

        // Search in title and description
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('issues.title', $search)
                ->orLike('issues.description', $search)
                ->orLike('issues.issue_key', $search)
                ->groupEnd();
        }

        // Access control - only show issues from projects user has access to
        if ($userId) {
            $projectIds = $this->projectService->getProjectsForUser($userId);
            $projectIds = array_column($projectIds, 'id');
            if (!empty($projectIds)) {
                $builder->whereIn('issues.project_id', $projectIds);
            } else {
                // User has no projects, return empty
                return [];
            }
        }

        return $builder->orderBy('issues.position', 'ASC')
            ->orderBy('issues.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}

