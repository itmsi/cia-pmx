<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\PermissionModel;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissionModel = new PermissionModel();
        
        $permissions = [
            // Role & Permission Management
            [
                'name' => 'Manage Roles',
                'slug' => 'manage-roles',
                'description' => 'Create, update, and delete roles'
            ],
            [
                'name' => 'Manage Permissions',
                'slug' => 'manage-permissions',
                'description' => 'Create, update, and delete permissions'
            ],
            [
                'name' => 'Assign Roles',
                'slug' => 'assign-roles',
                'description' => 'Assign roles to users'
            ],
            
            // Workspace Management
            [
                'name' => 'Manage Workspaces',
                'slug' => 'manage-workspaces',
                'description' => 'Create, update, and delete workspaces'
            ],
            [
                'name' => 'Manage Workspace Members',
                'slug' => 'manage-workspace-members',
                'description' => 'Add and remove members from workspaces'
            ],
            [
                'name' => 'View Workspaces',
                'slug' => 'view-workspaces',
                'description' => 'View workspace information'
            ],
            
            // Project Management
            [
                'name' => 'Manage Projects',
                'slug' => 'manage-projects',
                'description' => 'Create, update, and delete projects'
            ],
            [
                'name' => 'Manage Project Members',
                'slug' => 'manage-project-members',
                'description' => 'Add and remove members from projects'
            ],
            [
                'name' => 'View Projects',
                'slug' => 'view-projects',
                'description' => 'View project information'
            ],
            
            // Issue Management
            [
                'name' => 'Create Issues',
                'slug' => 'create-issues',
                'description' => 'Create new issues/tasks'
            ],
            [
                'name' => 'Update Issues',
                'slug' => 'update-issues',
                'description' => 'Update existing issues'
            ],
            [
                'name' => 'Delete Issues',
                'slug' => 'delete-issues',
                'description' => 'Delete issues'
            ],
            [
                'name' => 'View Issues',
                'slug' => 'view-issues',
                'description' => 'View issue information'
            ],
            [
                'name' => 'Assign Issues',
                'slug' => 'assign-issues',
                'description' => 'Assign issues to users'
            ],
            [
                'name' => 'Move Issues',
                'slug' => 'move-issues',
                'description' => 'Move issues between columns/statuses'
            ],
            
            // Label Management
            [
                'name' => 'Manage Labels',
                'slug' => 'manage-labels',
                'description' => 'Create, update, and delete labels'
            ],
            [
                'name' => 'Assign Labels',
                'slug' => 'assign-labels',
                'description' => 'Assign labels to issues'
            ],
            
            // Comments
            [
                'name' => 'Create Comments',
                'slug' => 'create-comments',
                'description' => 'Add comments to issues'
            ],
            [
                'name' => 'Update Comments',
                'slug' => 'update-comments',
                'description' => 'Update own comments'
            ],
            [
                'name' => 'Delete Comments',
                'slug' => 'delete-comments',
                'description' => 'Delete own comments'
            ],
            
            // Reports & Analytics
            [
                'name' => 'View Reports',
                'slug' => 'view-reports',
                'description' => 'Access reports and analytics'
            ],
            [
                'name' => 'View Activity Logs',
                'slug' => 'view-activity-logs',
                'description' => 'View system activity logs'
            ],
            
            // Board Management
            [
                'name' => 'Manage Boards',
                'slug' => 'manage-boards',
                'description' => 'Create, update, and delete boards'
            ],
            [
                'name' => 'Manage Columns',
                'slug' => 'manage-columns',
                'description' => 'Create, update, and delete columns'
            ]
        ];
        
        foreach ($permissions as $permission) {
            // Check if permission already exists by slug
            $existing = $permissionModel->where('slug', $permission['slug'])->first();
            
            if (!$existing) {
                $permissionModel->insert($permission);
                echo "Permission created: {$permission['name']} ({$permission['slug']})\n";
            } else {
                echo "Permission already exists: {$permission['name']} ({$permission['slug']})\n";
            }
        }
        
        echo "\nPermissions seeding completed!\n";
    }
}

