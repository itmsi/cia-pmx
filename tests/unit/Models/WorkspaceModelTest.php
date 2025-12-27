<?php

namespace Tests\Unit\Models;

use Tests\Support\TestCase;
use App\Models\WorkspaceModel;
use App\Models\UserModel;

/**
 * @internal
 */
final class WorkspaceModelTest extends TestCase
{
    protected WorkspaceModel $workspaceModel;
    protected UserModel $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workspaceModel = new WorkspaceModel();
        $this->userModel = new UserModel();
    }

    public function testCanCreateWorkspace(): void
    {
        $user = $this->createTestUser();
        
        $data = [
            'name' => 'Test Workspace',
            'slug' => 'test-workspace',
            'description' => 'Test workspace description',
            'owner_id' => $user['id']
        ];

        $id = $this->workspaceModel->insert($data);
        $this->assertIsInt($id);

        $workspace = $this->workspaceModel->find($id);
        $this->assertNotNull($workspace);
        $this->assertEquals('Test Workspace', $workspace['name']);
        $this->assertEquals($user['id'], $workspace['owner_id']);
    }

    public function testWorkspaceHasOwner(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);

        $this->assertEquals($user['id'], $workspace['owner_id']);
    }

    public function testCanUpdateWorkspace(): void
    {
        $user = $this->createTestUser();
        $id = $this->workspaceModel->insert([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'owner_id' => $user['id']
        ]);

        $this->workspaceModel->update($id, [
            'name' => 'Updated Name'
        ]);

        $workspace = $this->workspaceModel->find($id);
        $this->assertEquals('Updated Name', $workspace['name']);
    }

    public function testCanDeleteWorkspace(): void
    {
        $user = $this->createTestUser();
        $id = $this->workspaceModel->insert([
            'name' => 'Test Workspace',
            'slug' => 'test-workspace',
            'owner_id' => $user['id']
        ]);

        $this->workspaceModel->delete($id);
        $workspace = $this->workspaceModel->find($id);
        $this->assertNull($workspace);
    }
}

