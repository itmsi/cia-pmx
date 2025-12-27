<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\RoleModel;
use App\Models\PermissionModel;

class RolePermissionsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $roleModel = new RoleModel();
        $permissionModel = new PermissionModel();
        
        // Get all roles
        $roles = $roleModel->findAll();
        $rolesBySlug = [];
        foreach ($roles as $role) {
            $rolesBySlug[$role['slug']] = $role;
        }
        
        // Get all permissions
        $permissions = $permissionModel->findAll();
        $permissionsBySlug = [];
        foreach ($permissions as $permission) {
            $permissionsBySlug[$permission['slug']] = $permission;
        }
        
        // Define role-permission mappings
        $rolePermissions = [
            'admin' => [
                // Admin gets ALL permissions
                'manage-roles',
                'manage-permissions',
                'assign-roles',
                'manage-workspaces',
                'manage-workspace-members',
                'view-workspaces',
                'manage-projects',
                'manage-project-members',
                'view-projects',
                'create-issues',
                'update-issues',
                'delete-issues',
                'view-issues',
                'assign-issues',
                'move-issues',
                'manage-labels',
                'assign-labels',
                'create-comments',
                'update-comments',
                'delete-comments',
                'view-reports',
                'view-activity-logs',
                'manage-boards',
                'manage-columns'
            ],
            'project-manager' => [
                // Project Manager permissions
                'view-workspaces',
                'manage-projects',
                'manage-project-members',
                'view-projects',
                'create-issues',
                'update-issues',
                'delete-issues',
                'view-issues',
                'assign-issues',
                'move-issues',
                'manage-labels',
                'assign-labels',
                'create-comments',
                'update-comments',
                'delete-comments',
                'view-reports',
                'view-activity-logs',
                'manage-boards',
                'manage-columns'
            ],
            'developer' => [
                // Developer permissions
                'view-workspaces',
                'view-projects',
                'create-issues',
                'update-issues',
                'view-issues',
                'assign-issues', // Can assign to themselves
                'move-issues',
                'assign-labels',
                'create-comments',
                'update-comments',
                'delete-comments' // Own comments only
            ],
            'qa' => [
                // QA permissions
                'view-workspaces',
                'view-projects',
                'create-issues',
                'update-issues',
                'view-issues',
                'assign-issues',
                'move-issues',
                'assign-labels',
                'create-comments',
                'update-comments',
                'delete-comments',
                'view-reports'
            ],
            'viewer' => [
                // Viewer - read-only permissions
                'view-workspaces',
                'view-projects',
                'view-issues',
                'view-reports'
            ]
        ];
        
        // Clear existing role-permissions (optional - comment out if you want to keep existing)
        // $db->table('role_permissions')->truncate();
        
        $totalAssigned = 0;
        
        foreach ($rolePermissions as $roleSlug => $permissionSlugs) {
            if (!isset($rolesBySlug[$roleSlug])) {
                echo "Warning: Role '{$roleSlug}' not found. Skipping...\n";
                continue;
            }
            
            $roleId = $rolesBySlug[$roleSlug]['id'];
            
            foreach ($permissionSlugs as $permissionSlug) {
                if (!isset($permissionsBySlug[$permissionSlug])) {
                    echo "Warning: Permission '{$permissionSlug}' not found. Skipping...\n";
                    continue;
                }
                
                $permissionId = $permissionsBySlug[$permissionSlug]['id'];
                
                // Check if mapping already exists
                $exists = $db->table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->countAllResults() > 0;
                
                if (!$exists) {
                    $db->table('role_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId
                    ]);
                    $totalAssigned++;
                }
            }
            
            $count = count($permissionSlugs);
            echo "Assigned {$count} permissions to role: {$roleSlug}\n";
        }
        
        echo "\nTotal role-permission mappings created: {$totalAssigned}\n";
        echo "Role-Permissions seeding completed!\n";
    }
}

