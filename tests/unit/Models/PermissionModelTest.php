<?php

namespace Tests\Unit\Models;

use Tests\Support\TestCase;
use App\Models\PermissionModel;

/**
 * @internal
 */
final class PermissionModelTest extends TestCase
{
    protected PermissionModel $permissionModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissionModel = new PermissionModel();
    }

    public function testCanCreatePermission(): void
    {
        $data = [
            'name' => 'Create Project',
            'slug' => 'create-project',
            'description' => 'Permission to create projects'
        ];

        $id = $this->permissionModel->insert($data);
        $this->assertIsInt($id);

        $permission = $this->permissionModel->find($id);
        $this->assertNotNull($permission);
        $this->assertEquals('Create Project', $permission['name']);
        $this->assertEquals('create-project', $permission['slug']);
    }

    public function testCanUpdatePermission(): void
    {
        $id = $this->permissionModel->insert([
            'name' => 'Create Project',
            'slug' => 'create-project',
            'description' => 'Original description'
        ]);

        $this->permissionModel->update($id, [
            'description' => 'Updated description'
        ]);

        $permission = $this->permissionModel->find($id);
        $this->assertEquals('Updated description', $permission['description']);
    }

    public function testCanDeletePermission(): void
    {
        $id = $this->permissionModel->insert([
            'name' => 'Create Project',
            'slug' => 'create-project',
            'description' => 'Permission description'
        ]);

        $this->permissionModel->delete($id);
        $permission = $this->permissionModel->find($id);
        $this->assertNull($permission);
    }

    public function testCanFindPermissionBySlug(): void
    {
        $this->permissionModel->insert([
            'name' => 'Create Project',
            'slug' => 'create-project',
            'description' => 'Permission description'
        ]);

        $permission = $this->permissionModel->where('slug', 'create-project')->first();
        $this->assertNotNull($permission);
        $this->assertEquals('create-project', $permission['slug']);
    }
}

