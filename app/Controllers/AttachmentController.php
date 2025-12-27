<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AttachmentService;
use App\Services\IssueService;
use App\Services\ProjectService;
use CodeIgniter\HTTP\ResponseInterface;

class AttachmentController extends BaseController
{
    protected AttachmentService $attachmentService;
    protected IssueService $issueService;
    protected ProjectService $projectService;

    public function __construct()
    {
        $this->attachmentService = new AttachmentService();
        $this->issueService = new IssueService();
        $this->projectService = new ProjectService();
    }

    /**
     * Upload attachment for issue
     * POST /attachments
     */
    public function store()
    {
        $rules = [
            'issue_id' => 'required|integer',
            'file' => 'uploaded[file]|max_size[file,10240]', // 10MB max
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

            if (!$this->projectService->userHasAccess($issue['project_id'], $userId)) {
                throw new \RuntimeException('You do not have access to this issue');
            }

            $file = $this->request->getFile('file');
            $description = $this->request->getPost('description');

            $this->attachmentService->uploadAttachment(
                $issueId,
                $userId,
                $file,
                $description
            );

            return redirect()->back()
                ->with('success', 'File uploaded successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Download attachment
     * GET /attachments/{id}/download
     */
    public function download($id)
    {
        try {
            $userId = session()->get('user_id');
            $attachment = $this->attachmentService->getAttachmentById((int)$id);
            
            if (!$attachment) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Attachment not found');
            }

            // Check issue access
            $issue = $this->issueService->getIssueById($attachment['issue_id']);
            if (!$issue) {
                throw new \RuntimeException('Issue not found');
            }

            if (!$this->projectService->userHasAccess($issue['project_id'], $userId)) {
                throw new \RuntimeException('You do not have access to this attachment');
            }

            $fileContent = $this->attachmentService->getFileContent((int)$id);
            
            if (!$fileContent) {
                throw new \RuntimeException('File not found');
            }

            return $this->response->download($fileContent['path'], null)
                ->setFileName($fileContent['name']);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete attachment
     * POST /attachments/{id}/delete
     */
    public function delete($id)
    {
        try {
            $userId = session()->get('user_id');
            $this->attachmentService->deleteAttachment((int)$id, $userId);
            
            return redirect()->back()
                ->with('success', 'Attachment deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get attachments for issue (AJAX)
     * GET /attachments/issue/{issueId}
     */
    public function getByIssue($issueId)
    {
        try {
            $userId = session()->get('user_id');
            
            // Check issue access
            $issue = $this->issueService->getIssueById((int)$issueId);
            if (!$issue) {
                throw new \RuntimeException('Issue not found');
            }

            if (!$this->projectService->userHasAccess($issue['project_id'], $userId)) {
                throw new \RuntimeException('You do not have access to this issue');
            }

            $attachments = $this->attachmentService->getAttachmentsByIssue((int)$issueId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $attachments
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(400);
        }
    }
}
