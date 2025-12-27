<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ProjectService;
use App\Services\WorkspaceService;
use App\Services\RoleService;

class ProjectController extends BaseController
{
    protected ProjectService $projectService;
    protected WorkspaceService $workspaceService;
    protected RoleService $roleService;

    public function __construct()
    {
        $this->projectService = new ProjectService();
        $this->workspaceService = new WorkspaceService();
        $this->roleService = new RoleService();
    }

    /**
     * Display list of projects
     * GET /projects
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $workspaceId = $this->request->getGet('workspace_id');
        
        $projects = $this->projectService->getProjectsForUser($userId, $workspaceId ? (int)$workspaceId : null);
        $workspaces = $this->workspaceService->getWorkspacesForUser($userId);
        
        return view('projects/index', [
            'projects' => $projects,
            'workspaces' => $workspaces,
            'selectedWorkspace' => $workspaceId
        ]);
    }

    /**
     * Show create project form
     * GET /projects/create
     */
    public function create()
    {
        $userId = session()->get('user_id');
        $workspaces = $this->workspaceService->getWorkspacesForUser($userId);
        
        return view('projects/create', [
            'workspaces' => $workspaces
        ]);
    }

    /**
     * Store new project
     * POST /projects
     */
    public function store()
    {
        $rules = [
            'workspace_id' => 'required|integer',
            'key' => 'required|alpha_numeric|max_length[20]',
            'name' => 'required|min_length[3]|max_length[100]',
            'description' => 'permit_empty|max_length[1000]',
            'visibility' => 'permit_empty|in_list[private,workspace,public]',
            'status' => 'permit_empty|in_list[planning,active,on_hold,completed,archived]',
            'start_date' => 'permit_empty|valid_date',
            'end_date' => 'permit_empty|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $workspaceId = (int)$this->request->getPost('workspace_id');
            
            // Check workspace access
            if (!$this->workspaceService->userHasAccess($workspaceId, $userId)) {
                throw new \RuntimeException('You do not have access to this workspace');
            }

            $projectId = $this->projectService->createProject(
                $workspaceId,
                $this->request->getPost('key'),
                $this->request->getPost('name'),
                $userId,
                [
                    'description' => $this->request->getPost('description'),
                    'visibility' => $this->request->getPost('visibility') ?? 'private',
                    'status' => $this->request->getPost('status') ?? 'planning',
                    'start_date' => $this->request->getPost('start_date') ?: null,
                    'end_date' => $this->request->getPost('end_date') ?: null,
                ]
            );

            return redirect()->to('/projects')
                ->with('success', 'Project created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show project details
     * GET /projects/{id}
     */
    public function show($id)
    {
        $userId = session()->get('user_id');
        
        if (!$this->projectService->userHasAccess((int)$id, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found or access denied');
        }

        $project = $this->projectService->getProjectById((int)$id);
        $users = $this->projectService->getProjectUsers((int)$id);
        $isOwner = $this->projectService->isOwner((int)$id, $userId);

        return view('projects/show', [
            'project' => $project,
            'users' => $users,
            'isOwner' => $isOwner
        ]);
    }

    /**
     * Show edit project form
     * GET /projects/{id}/edit
     */
    public function edit($id)
    {
        $userId = session()->get('user_id');
        
        if (!$this->projectService->userHasAccess((int)$id, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found or access denied');
        }

        $project = $this->projectService->getProjectById((int)$id);
        
        return view('projects/edit', [
            'project' => $project
        ]);
    }

    /**
     * Update project
     * POST /projects/{id}
     */
    public function update($id)
    {
        $userId = session()->get('user_id');
        
        $rules = [
            'key' => 'required|alpha_numeric|max_length[20]',
            'name' => 'required|min_length[3]|max_length[100]',
            'description' => 'permit_empty|max_length[1000]',
            'visibility' => 'permit_empty|in_list[private,workspace,public]',
            'status' => 'permit_empty|in_list[planning,active,on_hold,completed,archived]',
            'start_date' => 'permit_empty|valid_date',
            'end_date' => 'permit_empty|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'key' => $this->request->getPost('key'),
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'visibility' => $this->request->getPost('visibility'),
                'status' => $this->request->getPost('status'),
                'start_date' => $this->request->getPost('start_date') ?: null,
                'end_date' => $this->request->getPost('end_date') ?: null,
            ];

            $this->projectService->updateProject((int)$id, $userId, $data);

            return redirect()->to("/projects/{$id}")
                ->with('success', 'Project updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete project
     * POST /projects/{id}/delete
     */
    public function delete($id)
    {
        $userId = session()->get('user_id');
        
        try {
            $this->projectService->deleteProject((int)$id, $userId);
            
            return redirect()->to('/projects')
                ->with('success', 'Project deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Add user to project
     * POST /projects/{id}/users
     */
    public function addUser($id)
    {
        $userId = session()->get('user_id');
        
        if (!$this->projectService->userHasAccess((int)$id, $userId)) {
            return redirect()->back()
                ->with('error', 'You do not have access to this project');
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
            $this->projectService->addUserToProject(
                (int)$id,
                (int)$this->request->getPost('user_id'),
                $this->request->getPost('role_id') ? (int)$this->request->getPost('role_id') : null
            );

            return redirect()->back()
                ->with('success', 'User added to project');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove user from project
     * POST /projects/{id}/users/{userId}/remove
     */
    public function removeUser($id, $userId)
    {
        $currentUserId = session()->get('user_id');
        
        if (!$this->projectService->isOwner((int)$id, $currentUserId)) {
            return redirect()->back()
                ->with('error', 'Only project owner can remove users');
        }

        try {
            $this->projectService->removeUserFromProject((int)$id, (int)$userId);
            
            return redirect()->back()
                ->with('success', 'User removed from project');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}

