<?php

namespace Tests\Unit\Services;

use Tests\Support\TestCase;
use App\Services\RoleService;
use App\Models\RoleModel;

/**
 * @internal
 */
final class RoleServiceTest extends TestCase
{
    protected RoleService $roleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleService = new RoleService();
    }

    public function testCanCreateRole(): void
    {
        $roleData = [
            'name' => 'Developer',
            'description' => 'Developer role description'
        ];

        $roleId = $this->roleService->createRole($roleData);
        $this->assertIsInt($roleId);

        $role = $this->roleService->getRoleById($roleId);
        $this->assertNotNull($role);
        $this->assertEquals('Developer', $role['name']);
        $this->assertEquals('developer', $role['slug']);
    }

    public function testCanGetAllRoles(): void
    {
        $this->roleService->createRole(['name' => 'Admin']);
        $this->roleService->createRole(['name' => 'Developer']);

        $roles = $this->roleService->getAllRoles();
        $this->assertIsArray($roles);
        $this->assertGreaterThanOrEqual(2, count($roles));
    }

    public function testCanGetRoleBySlug(): void
    {
        $this->roleService->createRole(['name' => 'Admin']);
        
        $role = $this->roleService->getRoleBySlug('admin');
        $this->assertNotNull($role);
        $this->assertEquals('admin', $role['slug']);
    }

    public function testCanUpdateRole(): void
    {
        $roleId = $this->roleService->createRole(['name' => 'Developer']);
        
        $updated = $this->roleService->updateRole($roleId, [
            'description' => 'Updated description'
        ]);
        
        $this->assertTrue($updated);
        
        $role = $this->roleService->getRoleById($roleId);
        $this->assertEquals('Updated description', $role['description']);
    }

    public function testCanDeleteRole(): void
    {
        $roleId = $this->roleService->createRole(['name' => 'Test Role']);
        
        $deleted = $this->roleService->deleteRole($roleId);
        $this->assertTrue($deleted);
        
        $role = $this->roleService->getRoleById($roleId);
        $this->assertNull($role);
    }
}

