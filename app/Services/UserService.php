<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Services\ActivityLogService;

class UserService
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;
    protected ActivityLogService $logService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->logService = new ActivityLogService();
    }

    /**
     * Get all users
     */
    public function getAllUsers(): array
    {
        return $this->userModel
            ->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->orderBy('users.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $id): ?array
    {
        $user = $this->userModel
            ->select('users.*, roles.name as role_name, roles.slug as role_slug')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->find($id);
        
        return $user;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?array
    {
        return $this->userModel->where('email', $email)->first();
    }

    /**
     * Create user
     */
    public function createUser(array $data): int
    {
        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Set default status if not provided
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        $id = $this->userModel->insert($data);

        // Log activity
        if (session()->get('user_id')) {
            $this->logService->log(
                'created',
                'user',
                $id,
                'User created: ' . ($data['email'] ?? 'N/A')
            );
        }

        return $id;
    }

    /**
     * Update user
     */
    public function updateUser(int $id, array $data): bool
    {
        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // Remove password from update if empty
            unset($data['password']);
        }

        $updated = $this->userModel->update($id, $data);

        if ($updated && session()->get('user_id')) {
            // Log activity
            $this->logService->log(
                'updated',
                'user',
                $id,
                'User updated: ' . ($data['email'] ?? 'N/A')
            );
        }

        return $updated;
    }

    /**
     * Delete user
     */
    public function deleteUser(int $id): bool
    {
        $currentUserId = session()->get('user_id');
        
        // Prevent self-deletion
        if ($id == $currentUserId) {
            throw new \RuntimeException('Cannot delete your own account');
        }

        $user = $this->getUserById($id);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        // Check if user is in any workspace or project
        $db = \Config\Database::connect();
        
        // Check workspace users
        $workspaceCount = $db->table('workspace_users')
            ->where('user_id', $id)
            ->countAllResults();
        
        // Check project users
        $projectCount = $db->table('project_users')
            ->where('user_id', $id)
            ->countAllResults();
        
        if ($workspaceCount > 0 || $projectCount > 0) {
            throw new \RuntimeException('Cannot delete user. User is assigned to workspaces or projects. Please remove user from all workspaces and projects first.');
        }

        $deleted = $this->userModel->delete($id);

        if ($deleted && $currentUserId) {
            // Log activity
            $this->logService->log(
                'deleted',
                'user',
                $id,
                'User deleted: ' . $user['email']
            );
        }

        return $deleted;
    }

    /**
     * Get all roles for dropdown
     */
    public function getAllRoles(): array
    {
        return $this->roleModel->findAll();
    }

    /**
     * Check if user has permission (helper method)
     */
    public function hasPermission(int $userId, string $permissionSlug): bool
    {
        $user = $this->getUserById($userId);
        if (!$user || !$user['role_id']) {
            return false;
        }

        $roleService = new RoleService();
        return $roleService->hasPermission($user['role_id'], $permissionSlug);
    }
}

