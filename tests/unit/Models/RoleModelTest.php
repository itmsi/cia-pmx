<?php

namespace Tests\Unit\Models;

use Tests\Support\TestCase;
use App\Models\RoleModel;

/**
 * @internal
 */
final class RoleModelTest extends TestCase
{
    protected RoleModel $roleModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleModel = new RoleModel();
    }

    public function testCanCreateRole(): void
    {
        $data = [
            'name' => 'Developer',
            'slug' => 'developer',
            'description' => 'Developer role'
        ];

        $id = $this->roleModel->insert($data);
        $this->assertIsInt($id);

        $role = $this->roleModel->find($id);
        $this->assertNotNull($role);
        $this->assertEquals('Developer', $role['name']);
        $this->assertEquals('developer', $role['slug']);
    }

    public function testCanUpdateRole(): void
    {
        $id = $this->roleModel->insert([
            'name' => 'Developer',
            'slug' => 'developer',
            'description' => 'Original description'
        ]);

        $this->roleModel->update($id, [
            'description' => 'Updated description'
        ]);

        $role = $this->roleModel->find($id);
        $this->assertEquals('Updated description', $role['description']);
    }

    public function testCanDeleteRole(): void
    {
        $id = $this->roleModel->insert([
            'name' => 'Developer',
            'slug' => 'developer',
            'description' => 'Developer role'
        ]);

        $this->roleModel->delete($id);
        $role = $this->roleModel->find($id);
        $this->assertNull($role);
    }

    public function testCanFindRoleBySlug(): void
    {
        $this->roleModel->insert([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin role'
        ]);

        $role = $this->roleModel->where('slug', 'admin')->first();
        $this->assertNotNull($role);
        $this->assertEquals('admin', $role['slug']);
    }

    public function testRoleHasTimestamps(): void
    {
        $id = $this->roleModel->insert([
            'name' => 'Developer',
            'slug' => 'developer',
            'description' => 'Developer role'
        ]);

        $role = $this->roleModel->find($id);
        $this->assertArrayHasKey('created_at', $role);
        $this->assertArrayHasKey('updated_at', $role);
    }
}

