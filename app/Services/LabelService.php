<?php

namespace App\Services;

use App\Models\LabelModel;
use App\Services\ActivityLogService;

class LabelService
{
    protected LabelModel $labelModel;
    protected ActivityLogService $logService;
    protected $db;

    public function __construct()
    {
        $this->labelModel = new LabelModel();
        $this->logService = new ActivityLogService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new label
     */
    public function createLabel(string $name, ?int $workspaceId = null, ?int $projectId = null, ?string $color = null, ?string $description = null): int
    {
        if (!$workspaceId && !$projectId) {
            throw new \RuntimeException('Label must belong to either workspace or project');
        }

        $labelId = $this->labelModel->insert([
            'workspace_id' => $workspaceId,
            'project_id' => $projectId,
            'name' => $name,
            'color' => $color ?? '#007bff',
            'description' => $description
        ]);

        $this->logService->log(
            'create',
            'label',
            $labelId,
            "Label created: {$name}"
        );

        return $labelId;
    }

    /**
     * Get all labels for workspace
     */
    public function getLabelsByWorkspace(int $workspaceId): array
    {
        return $this->labelModel
            ->where('workspace_id', $workspaceId)
            ->where('project_id', null)
            ->findAll();
    }

    /**
     * Get all labels for project
     */
    public function getLabelsByProject(int $projectId): array
    {
        // Get project-specific labels + workspace labels
        $projectLabels = $this->labelModel
            ->where('project_id', $projectId)
            ->findAll();

        // Get project to find workspace
        $project = $this->db->table('projects')->where('id', $projectId)->get()->getRowArray();
        if ($project) {
            $workspaceLabels = $this->labelModel
                ->where('workspace_id', $project['workspace_id'])
                ->where('project_id', null)
                ->findAll();

            return array_merge($projectLabels, $workspaceLabels);
        }

        return $projectLabels;
    }

    /**
     * Get label by ID
     */
    public function getLabelById(int $labelId): ?array
    {
        return $this->labelModel->find($labelId);
    }

    /**
     * Update label
     */
    public function updateLabel(int $labelId, array $data): bool
    {
        $result = $this->labelModel->update($labelId, $data);

        if ($result) {
            $this->logService->log(
                'update',
                'label',
                $labelId,
                'Label updated'
            );
        }

        return $result;
    }

    /**
     * Delete label
     */
    public function deleteLabel(int $labelId): bool
    {
        // Check if label is used by any issues
        $count = $this->db->table('issue_labels')
            ->where('label_id', $labelId)
            ->countAllResults();

        if ($count > 0) {
            throw new \RuntimeException("Cannot delete label: used by {$count} issue(s)");
        }

        $result = $this->labelModel->delete($labelId);

        if ($result) {
            $this->logService->log(
                'delete',
                'label',
                $labelId,
                'Label deleted'
            );
        }

        return $result;
    }

    /**
     * Add label to issue
     */
    public function addLabelToIssue(int $issueId, int $labelId): bool
    {
        // Check if already added
        $exists = $this->db->table('issue_labels')
            ->where('issue_id', $issueId)
            ->where('label_id', $labelId)
            ->countAllResults() > 0;

        if ($exists) {
            return true; // Already added
        }

        return $this->db->table('issue_labels')->insert([
            'issue_id' => $issueId,
            'label_id' => $labelId
        ]);
    }

    /**
     * Remove label from issue
     */
    public function removeLabelFromIssue(int $issueId, int $labelId): bool
    {
        return $this->db->table('issue_labels')
            ->where('issue_id', $issueId)
            ->where('label_id', $labelId)
            ->delete();
    }

    /**
     * Get labels for an issue
     */
    public function getIssueLabels(int $issueId): array
    {
        return $this->db->table('issue_labels')
            ->select('labels.*')
            ->join('labels', 'labels.id = issue_labels.label_id')
            ->where('issue_labels.issue_id', $issueId)
            ->get()
            ->getResultArray();
    }

    /**
     * Get issues with a specific label
     */
    public function getIssuesByLabel(int $labelId, ?int $projectId = null): array
    {
        $builder = $this->db->table('issues')
            ->select('issues.*')
            ->join('issue_labels', 'issue_labels.issue_id = issues.id')
            ->where('issue_labels.label_id', $labelId);

        if ($projectId) {
            $builder->where('issues.project_id', $projectId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Set labels for an issue (replace all)
     */
    public function setIssueLabels(int $issueId, array $labelIds): bool
    {
        $this->db->transStart();

        // Remove all existing labels
        $this->db->table('issue_labels')
            ->where('issue_id', $issueId)
            ->delete();

        // Add new labels
        foreach ($labelIds as $labelId) {
            $this->db->table('issue_labels')->insert([
                'issue_id' => $issueId,
                'label_id' => $labelId
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}

