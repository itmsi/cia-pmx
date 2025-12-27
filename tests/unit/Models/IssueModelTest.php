<?php

namespace Tests\Unit\Models;

use Tests\Support\TestCase;
use App\Models\IssueModel;
use App\Models\ColumnModel;
use App\Models\BoardModel;

/**
 * @internal
 */
final class IssueModelTest extends TestCase
{
    protected IssueModel $issueModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->issueModel = new IssueModel();
    }

    public function testCanCreateIssue(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);
        
        // Create board and column
        $boardModel = new BoardModel();
        $boardId = $boardModel->insert([
            'name' => 'Test Board',
            'project_id' => $project['id'],
            'user_id' => $user['id']
        ]);
        
        $columnModel = new ColumnModel();
        $columnId = $columnModel->insert([
            'board_id' => $boardId,
            'name' => 'To Do',
            'position' => 1
        ]);
        
        $data = [
            'column_id' => $columnId,
            'project_id' => $project['id'],
            'title' => 'Test Issue',
            'issue_type' => 'task',
            'priority' => 'medium',
            'description' => 'Test issue description',
            'assignee_id' => $user['id'],
            'reporter_id' => $user['id']
        ];

        $id = $this->issueModel->insert($data);
        $this->assertIsInt($id);

        $issue = $this->issueModel->find($id);
        $this->assertNotNull($issue);
        $this->assertEquals('Test Issue', $issue['title']);
        $this->assertEquals('task', $issue['issue_type']);
    }

    public function testIssueTypeValues(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);
        
        $boardModel = new BoardModel();
        $boardId = $boardModel->insert([
            'name' => 'Test Board',
            'project_id' => $project['id'],
            'user_id' => $user['id']
        ]);
        
        $columnModel = new ColumnModel();
        $columnId = $columnModel->insert([
            'board_id' => $boardId,
            'name' => 'To Do',
            'position' => 1
        ]);
        
        $types = ['task', 'bug', 'story', 'epic', 'sub_task'];
        
        foreach ($types as $type) {
            $id = $this->issueModel->insert([
                'column_id' => $columnId,
                'project_id' => $project['id'],
                'title' => "Test {$type}",
                'issue_type' => $type,
                'reporter_id' => $user['id']
            ]);
            
            $issue = $this->issueModel->find($id);
            $this->assertEquals($type, $issue['issue_type']);
        }
    }

    public function testIssuePriorityValues(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);
        
        $boardModel = new BoardModel();
        $boardId = $boardModel->insert([
            'name' => 'Test Board',
            'project_id' => $project['id'],
            'user_id' => $user['id']
        ]);
        
        $columnModel = new ColumnModel();
        $columnId = $columnModel->insert([
            'board_id' => $boardId,
            'name' => 'To Do',
            'position' => 1
        ]);
        
        $priorities = ['lowest', 'low', 'medium', 'high', 'critical'];
        
        foreach ($priorities as $priority) {
            $id = $this->issueModel->insert([
                'column_id' => $columnId,
                'project_id' => $project['id'],
                'title' => "Test Priority {$priority}",
                'priority' => $priority,
                'reporter_id' => $user['id']
            ]);
            
            $issue = $this->issueModel->find($id);
            $this->assertEquals($priority, $issue['priority']);
        }
    }

    public function testIssueCanHaveAssignee(): void
    {
        $user = $this->createTestUser();
        $assignee = $this->createTestUser(['email' => 'assignee@example.com']);
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);
        
        $boardModel = new BoardModel();
        $boardId = $boardModel->insert([
            'name' => 'Test Board',
            'project_id' => $project['id'],
            'user_id' => $user['id']
        ]);
        
        $columnModel = new ColumnModel();
        $columnId = $columnModel->insert([
            'board_id' => $boardId,
            'name' => 'To Do',
            'position' => 1
        ]);
        
        $id = $this->issueModel->insert([
            'column_id' => $columnId,
            'project_id' => $project['id'],
            'title' => 'Test Issue',
            'assignee_id' => $assignee['id'],
            'reporter_id' => $user['id']
        ]);

        $issue = $this->issueModel->find($id);
        $this->assertEquals($assignee['id'], $issue['assignee_id']);
    }
}

