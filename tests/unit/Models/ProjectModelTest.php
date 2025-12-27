<?php

namespace Tests\Unit\Models;

use Tests\Support\TestCase;
use App\Models\ProjectModel;
use App\Models\WorkspaceModel;

/**
 * @internal
 */
final class ProjectModelTest extends TestCase
{
    protected ProjectModel $projectModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectModel = new ProjectModel();
    }

    public function testCanCreateProject(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        
        $data = [
            'workspace_id' => $workspace['id'],
            'key' => 'TEST',
            'name' => 'Test Project',
            'description' => 'Test project description',
            'visibility' => 'private',
            'status' => 'active',
            'owner_id' => $user['id']
        ];

        $id = $this->projectModel->insert($data);
        $this->assertIsInt($id);

        $project = $this->projectModel->find($id);
        $this->assertNotNull($project);
        $this->assertEquals('TEST', $project['key']);
        $this->assertEquals('Test Project', $project['name']);
    }

    public function testProjectBelongsToWorkspace(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);

        $this->assertEquals($workspace['id'], $project['workspace_id']);
    }

    public function testProjectHasOwner(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);

        $this->assertEquals($user['id'], $project['owner_id']);
    }

    public function testProjectVisibilityValues(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        
        $visibilities = ['private', 'workspace', 'public'];
        
        foreach ($visibilities as $visibility) {
            $project = $this->createTestProject($workspace['id'], $user['id'], [
                'visibility' => $visibility
            ]);
            
            $this->assertEquals($visibility, $project['visibility']);
        }
    }

    public function testProjectStatusValues(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        
        $statuses = ['planning', 'active', 'on_hold', 'completed', 'archived'];
        
        foreach ($statuses as $status) {
            $project = $this->createTestProject($workspace['id'], $user['id'], [
                'status' => $status
            ]);
            
            $this->assertEquals($status, $project['status']);
        }
    }
}

