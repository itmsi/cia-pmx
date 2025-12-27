<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\WorkspaceService;
use App\Services\RoleService;

class WorkspaceController extends BaseController
{
    protected WorkspaceService $workspaceService;
    protected RoleService $roleService;

    public function __construct()
    {
        $this->workspaceService = new WorkspaceService();
        $this->roleService = new RoleService();
    }

    /**
     * Display list of workspaces
     * GET /workspaces
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $workspaces = $this->workspaceService->getWorkspacesForUser($userId);
        
        return view('workspaces/index', [
            'workspaces' => $workspaces
        ]);
    }

    /**
     * Show create workspace form
     * GET /workspaces/create
     */
    public function create()
    {
        return view('workspaces/create');
    }

    /**
     * Store new workspace
     * POST /workspaces
     */
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'description' => 'permit_empty|max_length[1000]',
            'timezone' => 'permit_empty|timezone'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $workspaceId = $this->workspaceService->createWorkspace(
                $this->request->getPost('name'),
                $userId,
                $this->request->getPost('description'),
                $this->request->getPost('timezone') ?? 'UTC'
            );

            return redirect()->to('/workspaces')
                ->with('success', 'Workspace created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show workspace details
     * GET /workspaces/{id}
     */
    public function show($id)
    {
        $userId = session()->get('user_id');
        
        if (!$this->workspaceService->userHasAccess((int)$id, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Workspace not found or access denied');
        }

        $workspace = $this->workspaceService->getWorkspaceById((int)$id);
        $users = $this->workspaceService->getWorkspaceUsers((int)$id);
        $isOwner = $this->workspaceService->isOwner((int)$id, $userId);

        return view('workspaces/show', [
            'workspace' => $workspace,
            'users' => $users,
            'isOwner' => $isOwner
        ]);
    }

    /**
     * Show edit workspace form
     * GET /workspaces/{id}/edit
     */
    public function edit($id)
    {
        $userId = session()->get('user_id');
        
        if (!$this->workspaceService->isOwner((int)$id, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Only workspace owner can edit');
        }

        $workspace = $this->workspaceService->getWorkspaceById((int)$id);
        
        return view('workspaces/edit', [
            'workspace' => $workspace
        ]);
    }

    /**
     * Update workspace
     * POST /workspaces/{id}
     */
    public function update($id)
    {
        $userId = session()->get('user_id');
        
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'description' => 'permit_empty|max_length[1000]',
            'timezone' => 'permit_empty|timezone'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'timezone' => $this->request->getPost('timezone') ?? 'UTC'
            ];

            $this->workspaceService->updateWorkspace((int)$id, $userId, $data);

            return redirect()->to("/workspaces/{$id}")
                ->with('success', 'Workspace updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete workspace
     * POST /workspaces/{id}/delete
     */
    public function delete($id)
    {
        $userId = session()->get('user_id');
        
        try {
            $this->workspaceService->deleteWorkspace((int)$id, $userId);
            
            return redirect()->to('/workspaces')
                ->with('success', 'Workspace deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Add user to workspace
     * POST /workspaces/{id}/users
     */
    public function addUser($id)
    {
        $userId = session()->get('user_id');
        
        if (!$this->workspaceService->isOwner((int)$id, $userId)) {
            return redirect()->back()
                ->with('error', 'Only workspace owner can add users');
        }

        $rules = [
            'user_id' => 'required|integer',
            'role_id' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $this->workspaceService->addUserToWorkspace(
                (int)$id,
                (int)$this->request->getPost('user_id'),
                $this->request->getPost('role_id') ? (int)$this->request->getPost('role_id') : null
            );

            return redirect()->back()
                ->with('success', 'User added to workspace');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove user from workspace
     * POST /workspaces/{id}/users/{userId}/remove
     */
    public function removeUser($id, $userId)
    {
        $currentUserId = session()->get('user_id');
        
        if (!$this->workspaceService->isOwner((int)$id, $currentUserId)) {
            return redirect()->back()
                ->with('error', 'Only workspace owner can remove users');
        }

        try {
            $this->workspaceService->removeUserFromWorkspace((int)$id, (int)$userId);
            
            return redirect()->back()
                ->with('success', 'User removed from workspace');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}

