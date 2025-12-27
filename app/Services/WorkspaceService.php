<?php

namespace App\Services;

use App\Models\WorkspaceModel;
use App\Services\ActivityLogService;

class WorkspaceService
{
    protected WorkspaceModel $workspaceModel;
    protected ActivityLogService $logService;
    protected $db;

    public function __construct()
    {
        $this->workspaceModel = new WorkspaceModel();
        $this->logService = new ActivityLogService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new workspace
     */
    public function createWorkspace(string $name, int $ownerId, ?string $description = null, ?string $timezone = 'UTC'): int
    {
        $slug = $this->generateSlug($name);
        
        $workspaceId = $this->workspaceModel->insert([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'timezone' => $timezone,
            'owner_id' => $ownerId
        ]);

        // Add owner as workspace user
        $this->addUserToWorkspace($workspaceId, $ownerId);

        $this->logService->log(
            'create',
            'workspace',
            $workspaceId,
            "Workspace created: {$name}"
        );

        return $workspaceId;
    }

    /**
     * Get all workspaces for a user
     */
    public function getWorkspacesForUser(int $userId): array
    {
        return $this->db->table('workspaces')
            ->select('workspaces.*')
            ->join('workspace_users', 'workspace_users.workspace_id = workspaces.id')
            ->where('workspace_users.user_id', $userId)
            ->get()
            ->getResultArray();
    }

    /**
     * Get workspace by ID
     */
    public function getWorkspaceById(int $workspaceId): ?array
    {
        return $this->workspaceModel->find($workspaceId);
    }

    /**
     * Get workspace by slug
     */
    public function getWorkspaceBySlug(string $slug): ?array
    {
        return $this->workspaceModel->where('slug', $slug)->first();
    }

    /**
     * Check if user has access to workspace
     */
    public function userHasAccess(int $workspaceId, int $userId): bool
    {
        $count = $this->db->table('workspace_users')
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $userId)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Check if user is workspace owner
     */
    public function isOwner(int $workspaceId, int $userId): bool
    {
        $workspace = $this->getWorkspaceById($workspaceId);
        return $workspace && $workspace['owner_id'] == $userId;
    }

    /**
     * Update workspace
     */
    public function updateWorkspace(int $workspaceId, int $userId, array $data): bool
    {
        // Check ownership
        if (!$this->isOwner($workspaceId, $userId)) {
            throw new \RuntimeException('Only workspace owner can update workspace');
        }

        if (isset($data['name'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }

        $result = $this->workspaceModel->update($workspaceId, $data);

        if ($result) {
            $this->logService->log(
                'update',
                'workspace',
                $workspaceId,
                'Workspace updated'
            );
        }

        return $result;
    }

    /**
     * Delete workspace
     */
    public function deleteWorkspace(int $workspaceId, int $userId): bool
    {
        if (!$this->isOwner($workspaceId, $userId)) {
            throw new \RuntimeException('Only workspace owner can delete workspace');
        }

        $result = $this->workspaceModel->delete($workspaceId);

        if ($result) {
            $this->logService->log(
                'delete',
                'workspace',
                $workspaceId,
                'Workspace deleted'
            );
        }

        return $result;
    }

    /**
     * Add user to workspace
     */
    public function addUserToWorkspace(int $workspaceId, int $userId, ?int $roleId = null): bool
    {
        // Check if already added
        $exists = $this->db->table('workspace_users')
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;

        if ($exists) {
            return true; // Already added
        }

        return $this->db->table('workspace_users')->insert([
            'workspace_id' => $workspaceId,
            'user_id' => $userId,
            'role_id' => $roleId,
            'joined_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Remove user from workspace
     */
    public function removeUserFromWorkspace(int $workspaceId, int $userId): bool
    {
        // Cannot remove owner
        if ($this->isOwner($workspaceId, $userId)) {
            throw new \RuntimeException('Cannot remove workspace owner');
        }

        return $this->db->table('workspace_users')
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Get users in workspace
     */
    public function getWorkspaceUsers(int $workspaceId): array
    {
        return $this->db->table('workspace_users')
            ->select('users.*, workspace_users.role_id, workspace_users.joined_at')
            ->join('users', 'users.id = workspace_users.user_id')
            ->where('workspace_users.workspace_id', $workspaceId)
            ->get()
            ->getResultArray();
    }

    /**
     * Generate slug from name
     */
    protected function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure uniqueness
        $baseSlug = $slug;
        $counter = 1;
        while ($this->workspaceModel->where('slug', $slug)->first()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

