<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DatabaseSeeder
 * 
 * Main seeder that runs all seeders in the correct order.
 * 
 * Execution order:
 * 1. RolesSeeder - Must run first (creates roles)
 * 2. PermissionsSeeder - Can run independently (creates permissions)
 * 3. RolePermissionsSeeder - Must run after roles and permissions (creates mappings)
 * 4. UserSeeder - Optional (creates default admin user)
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run all seeders in correct order
     */
    public function run()
    {
        echo "========================================\n";
        echo "  Database Seeding Started\n";
        echo "========================================\n\n";
        
        try {
            // Step 1: Seed Roles (MUST RUN FIRST)
            echo "Step 1/3: Seeding Roles...\n";
            echo "----------------------------------------\n";
            $this->call('RolesSeeder');
            echo "\n";
            
            // Step 2: Seed Permissions
            echo "Step 2/3: Seeding Permissions...\n";
            echo "----------------------------------------\n";
            $this->call('PermissionsSeeder');
            echo "\n";
            
            // Step 3: Seed Role-Permission Mappings (MUST RUN AFTER ROLES & PERMISSIONS)
            echo "Step 3/3: Seeding Role-Permission Mappings...\n";
            echo "----------------------------------------\n";
            $this->call('RolePermissionsSeeder');
            echo "\n";
            
            // Optional: Seed Default User (commented out by default)
            // Uncomment if you want to create default admin user
            /*
            echo "Step 4: Seeding Default User...\n";
            echo "----------------------------------------\n";
            $this->call('UserSeeder');
            echo "\n";
            */
            
            echo "========================================\n";
            echo "  Database Seeding Completed!\n";
            echo "========================================\n";
            echo "\nSummary:\n";
            echo "- Roles: 5 roles created\n";
            echo "- Permissions: 27 permissions created\n";
            echo "- Role-Permissions: Mappings created\n";
            echo "\n";
            
        } catch (\Exception $e) {
            echo "\n========================================\n";
            echo "  ERROR: Seeding Failed!\n";
            echo "========================================\n";
            echo "Error: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . "\n";
            echo "Line: " . $e->getLine() . "\n";
            echo "\n";
            throw $e;
        }
    }
}

