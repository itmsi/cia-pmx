<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\LabelService;
use App\Services\ProjectService;
use App\Services\WorkspaceService;

class LabelController extends BaseController
{
    protected LabelService $labelService;
    protected ProjectService $projectService;
    protected WorkspaceService $workspaceService;

    public function __construct()
    {
        $this->labelService = new LabelService();
        $this->projectService = new ProjectService();
        $this->workspaceService = new WorkspaceService();
    }

    /**
     * Store new label
     * POST /labels
     */
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[1]|max_length[50]',
            'color' => 'permit_empty|max_length[7]',
            'workspace_id' => 'permit_empty|integer',
            'project_id' => 'permit_empty|integer',
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $workspaceId = $this->request->getPost('workspace_id') ? (int)$this->request->getPost('workspace_id') : null;
            $projectId = $this->request->getPost('project_id') ? (int)$this->request->getPost('project_id') : null;

            // Check access
            if ($workspaceId && !$this->workspaceService->userHasAccess($workspaceId, $userId)) {
                throw new \RuntimeException('You do not have access to this workspace');
            }
            
            if ($projectId && !$this->projectService->userHasAccess($projectId, $userId)) {
                throw new \RuntimeException('You do not have access to this project');
            }

            $this->labelService->createLabel(
                $this->request->getPost('name'),
                $workspaceId,
                $projectId,
                $this->request->getPost('color'),
                $this->request->getPost('description')
            );

            return redirect()->back()
                ->with('success', 'Label created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update label
     * POST /labels/{id}
     */
    public function update($id)
    {
        $rules = [
            'name' => 'required|min_length[1]|max_length[50]',
            'color' => 'permit_empty|max_length[7]',
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'color' => $this->request->getPost('color'),
                'description' => $this->request->getPost('description')
            ];

            $this->labelService->updateLabel((int)$id, $data);

            return redirect()->back()
                ->with('success', 'Label updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete label
     * POST /labels/{id}/delete
     */
    public function delete($id)
    {
        try {
            $this->labelService->deleteLabel((int)$id);
            
            return redirect()->back()
                ->with('success', 'Label deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Add label to issue (AJAX)
     * POST /labels/{id}/issues/{issueId}
     */
    public function addToIssue($id, $issueId)
    {
        try {
            $this->labelService->addLabelToIssue((int)$issueId, (int)$id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Label added to issue'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(400);
        }
    }

    /**
     * Remove label from issue (AJAX)
     * POST /labels/{id}/issues/{issueId}/remove
     */
    public function removeFromIssue($id, $issueId)
    {
        try {
            $this->labelService->removeLabelFromIssue((int)$issueId, (int)$id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Label removed from issue'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(400);
        }
    }
}

