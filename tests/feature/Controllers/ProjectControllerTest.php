<?php

namespace Tests\Feature\Controllers;

use Tests\Support\TestCase;

/**
 * @internal
 */
final class ProjectControllerTest extends TestCase
{
    public function testCanAccessProjectsIndex(): void
    {
        $user = $this->createTestUser();
        $this->loginUser($user['id']);

        $result = $this->get('/projects');
        $result->assertOK();
        $result->assertSee('Projects');
    }

    public function testCanAccessCreateProjectForm(): void
    {
        $user = $this->createTestUser();
        $this->loginUser($user['id']);

        $result = $this->get('/projects/create');
        $result->assertOK();
    }

    public function testCanCreateProject(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $this->loginUser($user['id']);

        $result = $this->post('/projects', [
            'workspace_id' => $workspace['id'],
            'key' => 'TEST',
            'name' => 'Test Project',
            'visibility' => 'private',
            'status' => 'active'
        ]);

        $result->assertRedirect();
        
        // Verify project was created
        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->where('key', 'TEST')->first();
        $this->assertNotNull($project);
    }
}

