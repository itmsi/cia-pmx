<?php

namespace App\Services;

use App\Models\ProjectModel;
use App\Services\ActivityLogService;

class ProjectService
{
    protected ProjectModel $projectModel;
    protected ActivityLogService $logService;
    protected $db;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
        $this->logService = new ActivityLogService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new project
     */
    public function createProject(int $workspaceId, string $key, string $name, int $ownerId, array $options = []): int
    {
        // Validate key uniqueness in workspace
        $this->validateProjectKey($workspaceId, $key);

        $projectId = $this->projectModel->insert([
            'workspace_id' => $workspaceId,
            'key' => strtoupper($key),
            'name' => $name,
            'description' => $options['description'] ?? null,
            'visibility' => $options['visibility'] ?? 'private',
            'status' => $options['status'] ?? 'planning',
            'owner_id' => $ownerId,
            'start_date' => $options['start_date'] ?? null,
            'end_date' => $options['end_date'] ?? null,
        ]);

        // Add owner as project user
        $this->addUserToProject($projectId, $ownerId);

        $this->logService->log(
            'create',
            'project',
            $projectId,
            "Project created: {$name}"
        );

        return $projectId;
    }

    /**
     * Get all projects for a user
     */
    public function getProjectsForUser(int $userId, ?int $workspaceId = null): array
    {
        $builder = $this->db->table('projects')
            ->select('projects.*')
            ->join('project_users', 'project_users.project_id = projects.id')
            ->where('project_users.user_id', $userId);

        if ($workspaceId) {
            $builder->where('projects.workspace_id', $workspaceId);
        }

        return $builder->orderBy('projects.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get project by ID
     */
    public function getProjectById(int $projectId): ?array
    {
        return $this->projectModel->find($projectId);
    }

    /**
     * Get project by key
     */
    public function getProjectByKey(int $workspaceId, string $key): ?array
    {
        return $this->projectModel
            ->where('workspace_id', $workspaceId)
            ->where('key', strtoupper($key))
            ->first();
    }

    /**
     * Check if user has access to project
     */
    public function userHasAccess(int $projectId, int $userId): bool
    {
        $project = $this->getProjectById($projectId);
        if (!$project) {
            return false;
        }

        // Owner always has access
        if ($project['owner_id'] == $userId) {
            return true;
        }

        // Check visibility
        if ($project['visibility'] === 'public') {
            return true; // Public projects are readable by all
        }

        // Check if user is in project
        $count = $this->db->table('project_users')
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Check if user is project owner
     */
    public function isOwner(int $projectId, int $userId): bool
    {
        $project = $this->getProjectById($projectId);
        return $project && $project['owner_id'] == $userId;
    }

    /**
     * Update project
     */
    public function updateProject(int $projectId, int $userId, array $data): bool
    {
        // Check access
        if (!$this->userHasAccess($projectId, $userId)) {
            throw new \RuntimeException('User does not have access to this project');
        }

        // Validate key if changed
        if (isset($data['key'])) {
            $project = $this->getProjectById($projectId);
            if ($project['key'] !== strtoupper($data['key'])) {
                $this->validateProjectKey($project['workspace_id'], $data['key'], $projectId);
            }
            $data['key'] = strtoupper($data['key']);
        }

        $result = $this->projectModel->update($projectId, $data);

        if ($result) {
            $this->logService->log(
                'update',
                'project',
                $projectId,
                'Project updated'
            );
        }

        return $result;
    }

    /**
     * Delete project
     */
    public function deleteProject(int $projectId, int $userId): bool
    {
        if (!$this->isOwner($projectId, $userId)) {
            throw new \RuntimeException('Only project owner can delete project');
        }

        $result = $this->projectModel->delete($projectId);

        if ($result) {
            $this->logService->log(
                'delete',
                'project',
                $projectId,
                'Project deleted'
            );
        }

        return $result;
    }

    /**
     * Add user to project
     */
    public function addUserToProject(int $projectId, int $userId, ?int $roleId = null): bool
    {
        // Check if already added
        $exists = $this->db->table('project_users')
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;

        if ($exists) {
            return true; // Already added
        }

        return $this->db->table('project_users')->insert([
            'project_id' => $projectId,
            'user_id' => $userId,
            'role_id' => $roleId,
            'joined_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Remove user from project
     */
    public function removeUserFromProject(int $projectId, int $userId): bool
    {
        // Cannot remove owner
        if ($this->isOwner($projectId, $userId)) {
            throw new \RuntimeException('Cannot remove project owner');
        }

        return $this->db->table('project_users')
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Get users in project
     */
    public function getProjectUsers(int $projectId): array
    {
        return $this->db->table('project_users')
            ->select('users.*, project_users.role_id, project_users.joined_at')
            ->join('users', 'users.id = project_users.user_id')
            ->where('project_users.project_id', $projectId)
            ->get()
            ->getResultArray();
    }

    /**
     * Generate next issue key for project
     */
    public function generateIssueKey(int $projectId): string
    {
        $project = $this->getProjectById($projectId);
        if (!$project) {
            throw new \RuntimeException('Project not found');
        }

        // Get last issue number
        $lastIssue = $this->db->table('issues')
            ->where('project_id', $projectId)
            ->orderBy('id', 'DESC')
            ->get(1)
            ->getRowArray();

        $nextNumber = 1;
        if ($lastIssue && isset($lastIssue['issue_key'])) {
            // Extract number from issue_key (e.g., MSI-5 -> 5)
            if (preg_match('/-(\d+)$/', $lastIssue['issue_key'], $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            }
        }

        return $project['key'] . '-' . $nextNumber;
    }

    /**
     * Validate project key uniqueness
     */
    protected function validateProjectKey(int $workspaceId, string $key, ?int $excludeProjectId = null): void
    {
        $builder = $this->projectModel
            ->where('workspace_id', $workspaceId)
            ->where('key', strtoupper($key));

        if ($excludeProjectId) {
            $builder->where('id !=', $excludeProjectId);
        }

        if ($builder->first()) {
            throw new \RuntimeException("Project key '{$key}' already exists in this workspace");
        }
    }
}

