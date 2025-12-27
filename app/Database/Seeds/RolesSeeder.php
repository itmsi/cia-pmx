<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\RoleModel;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $roleModel = new RoleModel();
        
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full system access. Can manage all resources including roles, permissions, workspaces, and projects.'
            ],
            [
                'name' => 'Project Manager',
                'slug' => 'project-manager',
                'description' => 'Can manage projects, assign issues, view reports, and manage project members.'
            ],
            [
                'name' => 'Developer',
                'slug' => 'developer',
                'description' => 'Can create and update issues, assign issues to themselves, and update issue status.'
            ],
            [
                'name' => 'QA',
                'slug' => 'qa',
                'description' => 'Quality Assurance. Can create, update issues, verify bugs, and access reports.'
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Read-only access. Can view projects, issues, and reports but cannot modify anything.'
            ]
        ];
        
        // Clear existing roles if needed (optional - comment out if you want to keep existing)
        // $roleModel->truncate();
        
        foreach ($roles as $role) {
            // Check if role already exists by slug
            $existing = $roleModel->where('slug', $role['slug'])->first();
            
            if (!$existing) {
                $roleModel->insert($role);
                echo "Role created: {$role['name']} ({$role['slug']})\n";
            } else {
                echo "Role already exists: {$role['name']} ({$role['slug']})\n";
            }
        }
        
        echo "\nRoles seeding completed!\n";
    }
}

