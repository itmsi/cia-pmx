<?php

namespace Tests\Unit\Services;

use Tests\Support\TestCase;
use App\Services\ProjectService;
use App\Models\UserModel;

/**
 * @internal
 */
final class ProjectServiceTest extends TestCase
{
    protected ProjectService $projectService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectService = new ProjectService();
    }

    public function testCanCreateProject(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        
        $projectData = [
            'workspace_id' => $workspace['id'],
            'key' => 'TEST',
            'name' => 'Test Project',
            'description' => 'Test description',
            'visibility' => 'private',
            'status' => 'active',
            'owner_id' => $user['id']
        ];

        $projectId = $this->projectService->createProject($user['id'], $projectData);
        $this->assertIsInt($projectId);

        $project = $this->projectService->getProjectById($projectId);
        $this->assertNotNull($project);
        $this->assertEquals('TEST', $project['key']);
    }

    public function testCanGenerateIssueKey(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id'], ['key' => 'TEST']);

        $key1 = $this->projectService->generateIssueKey($project['id']);
        $this->assertEquals('TEST-1', $key1);

        $key2 = $this->projectService->generateIssueKey($project['id']);
        $this->assertEquals('TEST-2', $key2);
    }

    public function testCanGetProjectsForUser(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project1 = $this->createTestProject($workspace['id'], $user['id']);
        
        // Add user to project
        $db = \Config\Database::connect();
        $db->table('project_users')->insert([
            'project_id' => $project1['id'],
            'user_id' => $user['id'],
            'role_id' => 1
        ]);

        $projects = $this->projectService->getProjectsForUser($user['id']);
        $this->assertIsArray($projects);
        $this->assertGreaterThanOrEqual(1, count($projects));
    }

    public function testCanCheckUserAccess(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);

        // Owner should have access
        $hasAccess = $this->projectService->userHasAccess($project['id'], $user['id']);
        $this->assertTrue($hasAccess);

        // Other user should not have access (private project)
        $otherUser = $this->createTestUser(['email' => 'other@example.com']);
        $hasAccess = $this->projectService->userHasAccess($project['id'], $otherUser['id']);
        $this->assertFalse($hasAccess);
    }
}

