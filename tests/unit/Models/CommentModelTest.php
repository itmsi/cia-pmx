<?php

namespace Tests\Unit\Models;

use Tests\Support\TestCase;
use App\Models\CommentModel;
use App\Models\IssueModel;

/**
 * @internal
 */
final class CommentModelTest extends TestCase
{
    protected CommentModel $commentModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentModel = new CommentModel();
    }

    public function testCanCreateComment(): void
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
        
        $issueModel = new IssueModel();
        $issueId = $issueModel->insert([
            'column_id' => $columnId,
            'project_id' => $project['id'],
            'title' => 'Test Issue',
            'reporter_id' => $user['id']
        ]);
        
        $data = [
            'issue_id' => $issueId,
            'user_id' => $user['id'],
            'content' => 'This is a test comment'
        ];

        $id = $this->commentModel->insert($data);
        $this->assertIsInt($id);

        $comment = $this->commentModel->find($id);
        $this->assertNotNull($comment);
        $this->assertEquals('This is a test comment', $comment['content']);
        $this->assertEquals($issueId, $comment['issue_id']);
    }

    public function testCommentCanHaveParent(): void
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
        
        $issueModel = new IssueModel();
        $issueId = $issueModel->insert([
            'column_id' => $columnId,
            'project_id' => $project['id'],
            'title' => 'Test Issue',
            'reporter_id' => $user['id']
        ]);
        
        $parentId = $this->commentModel->insert([
            'issue_id' => $issueId,
            'user_id' => $user['id'],
            'content' => 'Parent comment'
        ]);

        $childId = $this->commentModel->insert([
            'issue_id' => $issueId,
            'user_id' => $user['id'],
            'content' => 'Reply comment',
            'parent_id' => $parentId
        ]);

        $child = $this->commentModel->find($childId);
        $this->assertEquals($parentId, $child['parent_id']);
    }
}

