<?php

namespace App\Services;

use App\Models\PermissionModel;
use App\Services\ActivityLogService;

class PermissionService
{
    protected PermissionModel $permissionModel;
    protected ActivityLogService $logService;
    protected $db;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->logService = new ActivityLogService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new permission
     */
    public function createPermission(string $name, string $description = null): int
    {
        $slug = $this->generateSlug($name);
        
        $permissionId = $this->permissionModel->insert([
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ]);

        $this->logService->log(
            'create',
            'permission',
            $permissionId,
            "Permission created: {$name}"
        );

        return $permissionId;
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions(): array
    {
        return $this->permissionModel->findAll();
    }

    /**
     * Get permission by ID
     */
    public function getPermissionById(int $permissionId): ?array
    {
        return $this->permissionModel->find($permissionId);
    }

    /**
     * Get permission by slug
     */
    public function getPermissionBySlug(string $slug): ?array
    {
        return $this->permissionModel->where('slug', $slug)->first();
    }

    /**
     * Update permission
     */
    public function updatePermission(int $permissionId, string $name, string $description = null): bool
    {
        $slug = $this->generateSlug($name);
        
        $result = $this->permissionModel->update($permissionId, [
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ]);

        if ($result) {
            $this->logService->log(
                'update',
                'permission',
                $permissionId,
                "Permission updated: {$name}"
            );
        }

        return $result;
    }

    /**
     * Delete permission
     */
    public function deletePermission(int $permissionId): bool
    {
        // Check if permission is assigned to any role
        $count = $this->db->table('role_permissions')
            ->where('permission_id', $permissionId)
            ->countAllResults();

        if ($count > 0) {
            throw new \RuntimeException("Cannot delete permission: assigned to {$count} role(s)");
        }

        $result = $this->permissionModel->delete($permissionId);

        if ($result) {
            $this->logService->log(
                'delete',
                'permission',
                $permissionId,
                'Permission deleted'
            );
        }

        return $result;
    }

    /**
     * Generate slug from name
     */
    protected function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = trim($slug, '_');

        // Ensure uniqueness
        $baseSlug = $slug;
        $counter = 1;
        while ($this->permissionModel->where('slug', $slug)->first()) {
            $slug = $baseSlug . '_' . $counter;
            $counter++;
        }

        return $slug;
    }
}

