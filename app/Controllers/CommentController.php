<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\CommentService;
use App\Services\IssueService;

class CommentController extends BaseController
{
    protected CommentService $commentService;
    protected IssueService $issueService;

    public function __construct()
    {
        $this->commentService = new CommentService();
        $this->issueService = new IssueService();
    }

    /**
     * Store new comment
     * POST /comments
     */
    public function store()
    {
        $rules = [
            'issue_id' => 'required|integer',
            'content' => 'required|min_length[1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $issueId = (int)$this->request->getPost('issue_id');
            
            // Check issue access
            $issue = $this->issueService->getIssueById($issueId);
            if (!$issue) {
                throw new \RuntimeException('Issue not found');
            }

            $projectService = new \App\Services\ProjectService();
            if (!$projectService->userHasAccess($issue['project_id'], $userId)) {
                throw new \RuntimeException('You do not have access to this issue');
            }

            $this->commentService->createComment(
                $issueId,
                $userId,
                $this->request->getPost('content')
            );

            return redirect()->back()
                ->with('success', 'Comment added successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update comment
     * POST /comments/{id}
     */
    public function update($id)
    {
        $rules = [
            'content' => 'required|min_length[1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $this->commentService->updateComment(
                (int)$id,
                $userId,
                $this->request->getPost('content')
            );

            return redirect()->back()
                ->with('success', 'Comment updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete comment
     * POST /comments/{id}/delete
     */
    public function delete($id)
    {
        try {
            $userId = session()->get('user_id');
            $this->commentService->deleteComment((int)$id, $userId);
            
            return redirect()->back()
                ->with('success', 'Comment deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get comments for issue (AJAX)
     * GET /comments/issue/{issueId}
     */
    public function getByIssue($issueId)
    {
        try {
            $comments = $this->commentService->getCommentsByIssue((int)$issueId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(400);
        }
    }
}

