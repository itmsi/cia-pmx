<?php

namespace App\Services;

use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Services\ActivityLogService;

class RoleService
{
    protected RoleModel $roleModel;
    protected PermissionModel $permissionModel;
    protected ActivityLogService $logService;
    protected $db;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->logService = new ActivityLogService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new role
     */
    public function createRole(string $name, string $description = null): int
    {
        $slug = $this->generateSlug($name);
        
        $roleId = $this->roleModel->insert([
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ]);

        $this->logService->log(
            'create',
            'role',
            $roleId,
            "Role created: {$name}"
        );

        return $roleId;
    }

    /**
     * Get all roles
     */
    public function getAllRoles(): array
    {
        return $this->roleModel->findAll();
    }

    /**
     * Get role by ID
     */
    public function getRoleById(int $roleId): ?array
    {
        return $this->roleModel->find($roleId);
    }

    /**
     * Get role by slug
     */
    public function getRoleBySlug(string $slug): ?array
    {
        return $this->roleModel->where('slug', $slug)->first();
    }

    /**
     * Update role
     */
    public function updateRole(int $roleId, string $name, string $description = null): bool
    {
        $slug = $this->generateSlug($name);
        
        $result = $this->roleModel->update($roleId, [
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ]);

        if ($result) {
            $this->logService->log(
                'update',
                'role',
                $roleId,
                "Role updated: {$name}"
            );
        }

        return $result;
    }

    /**
     * Delete role
     */
    public function deleteRole(int $roleId): bool
    {
        // Check if role is used by any user
        $userCount = $this->db->table('users')
            ->where('role_id', $roleId)
            ->countAllResults();

        if ($userCount > 0) {
            throw new \RuntimeException("Cannot delete role: {$userCount} user(s) are using this role");
        }

        $result = $this->roleModel->delete($roleId);

        if ($result) {
            $this->logService->log(
                'delete',
                'role',
                $roleId,
                'Role deleted'
            );
        }

        return $result;
    }

    /**
     * Assign permission to role
     */
    public function assignPermission(int $roleId, int $permissionId): bool
    {
        // Check if already assigned
        $exists = $this->db->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->countAllResults() > 0;

        if ($exists) {
            return true; // Already assigned
        }

        return $this->db->table('role_permissions')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }

    /**
     * Remove permission from role
     */
    public function removePermission(int $roleId, int $permissionId): bool
    {
        return $this->db->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete();
    }

    /**
     * Get permissions for a role
     */
    public function getRolePermissions(int $roleId): array
    {
        return $this->db->table('role_permissions')
            ->select('permissions.*')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId)
            ->get()
            ->getResultArray();
    }

    /**
     * Check if role has permission
     */
    public function hasPermission(int $roleId, string $permissionSlug): bool
    {
        $count = $this->db->table('role_permissions')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId)
            ->where('permissions.slug', $permissionSlug)
            ->countAllResults();

        return $count > 0;
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
        while ($this->roleModel->where('slug', $slug)->first()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

