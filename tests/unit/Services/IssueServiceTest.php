<?php

namespace Tests\Unit\Services;

use Tests\Support\TestCase;
use App\Services\IssueService;
use App\Models\IssueModel;

/**
 * @internal
 */
final class IssueServiceTest extends TestCase
{
    protected IssueService $issueService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->issueService = new IssueService();
    }

    public function testCanCreateIssue(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);
        
        $boardModel = new \App\Models\BoardModel();
        $boardId = $boardModel->insert([
            'name' => 'Test Board',
            'project_id' => $project['id'],
            'user_id' => $user['id']
        ]);
        
        $columnModel = new \App\Models\ColumnModel();
        $columnId = $columnModel->insert([
            'board_id' => $boardId,
            'name' => 'To Do',
            'position' => 1
        ]);
        
        $issueData = [
            'project_id' => $project['id'],
            'column_id' => $columnId,
            'title' => 'Test Issue',
            'issue_type' => 'task',
            'priority' => 'medium',
            'description' => 'Test description',
            'reporter_id' => $user['id']
        ];

        $issueId = $this->issueService->createIssue($user['id'], $issueData);
        $this->assertIsInt($issueId);

        $issue = $this->issueService->getIssueById($issueId);
        $this->assertNotNull($issue);
        $this->assertEquals('Test Issue', $issue['title']);
    }

    public function testCanUpdateIssue(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);
        
        $boardModel = new \App\Models\BoardModel();
        $boardId = $boardModel->insert([
            'name' => 'Test Board',
            'project_id' => $project['id'],
            'user_id' => $user['id']
        ]);
        
        $columnModel = new \App\Models\ColumnModel();
        $columnId = $columnModel->insert([
            'board_id' => $boardId,
            'name' => 'To Do',
            'position' => 1
        ]);
        
        $issueId = $this->issueService->createIssue($user['id'], [
            'project_id' => $project['id'],
            'column_id' => $columnId,
            'title' => 'Original Title',
            'reporter_id' => $user['id']
        ]);

        $updated = $this->issueService->updateIssue($issueId, $user['id'], [
            'title' => 'Updated Title'
        ]);
        
        $this->assertTrue($updated);
        
        $issue = $this->issueService->getIssueById($issueId);
        $this->assertEquals('Updated Title', $issue['title']);
    }

    public function testCanAssignIssue(): void
    {
        $user = $this->createTestUser();
        $assignee = $this->createTestUser(['email' => 'assignee@example.com']);
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);
        
        $boardModel = new \App\Models\BoardModel();
        $boardId = $boardModel->insert([
            'name' => 'Test Board',
            'project_id' => $project['id'],
            'user_id' => $user['id']
        ]);
        
        $columnModel = new \App\Models\ColumnModel();
        $columnId = $columnModel->insert([
            'board_id' => $boardId,
            'name' => 'To Do',
            'position' => 1
        ]);
        
        $issueId = $this->issueService->createIssue($user['id'], [
            'project_id' => $project['id'],
            'column_id' => $columnId,
            'title' => 'Test Issue',
            'reporter_id' => $user['id']
        ]);

        $assigned = $this->issueService->assignIssue($issueId, $assignee['id'], $user['id']);
        $this->assertTrue($assigned);
        
        $issue = $this->issueService->getIssueById($issueId);
        $this->assertEquals($assignee['id'], $issue['assignee_id']);
    }
}

