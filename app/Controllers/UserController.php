<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\UserService;
use App\Services\RoleService;

class UserController extends BaseController
{
    protected UserService $userService;
    protected RoleService $roleService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->roleService = new RoleService();
    }

    /**
     * Display list of users
     * GET /users
     */
    public function index()
    {
        $users = $this->userService->getAllUsers();
        
        return view('users/index', [
            'users' => $users
        ]);
    }

    /**
     * Show create user form
     * GET /users/create
     */
    public function create()
    {
        $roles = $this->userService->getAllRoles();
        
        return view('users/create', [
            'roles' => $roles
        ]);
    }

    /**
     * Store new user
     * POST /users
     */
    public function store()
    {
        $rules = [
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'full_name' => 'permit_empty|max_length[100]',
            'phone' => 'permit_empty|max_length[20]',
            'status' => 'permit_empty|in_list[active,inactive]',
            'role_id' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'email' => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'full_name' => $this->request->getPost('full_name'),
                'phone' => $this->request->getPost('phone'),
                'status' => $this->request->getPost('status') ?: 'active',
                'role_id' => $this->request->getPost('role_id') ?: null
            ];

            $userId = $this->userService->createUser($data);
            
            return redirect()->to("/users/{$userId}")
                ->with('success', 'User created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show user details
     * GET /users/{id}
     */
    public function show($id)
    {
        $user = $this->userService->getUserById((int)$id);
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        // Get user's workspaces
        $db = \Config\Database::connect();
        $workspaces = $db->table('workspace_users')
            ->select('workspaces.*, workspace_users.role_id as workspace_role_id')
            ->join('workspaces', 'workspaces.id = workspace_users.workspace_id')
            ->where('workspace_users.user_id', $id)
            ->get()
            ->getResultArray();

        // Get user's projects
        $projects = $db->table('project_users')
            ->select('projects.*, project_users.role_id as project_role_id')
            ->join('projects', 'projects.id = project_users.project_id')
            ->where('project_users.user_id', $id)
            ->get()
            ->getResultArray();

        return view('users/show', [
            'user' => $user,
            'workspaces' => $workspaces,
            'projects' => $projects
        ]);
    }

    /**
     * Show edit user form
     * GET /users/{id}/edit
     */
    public function edit($id)
    {
        $user = $this->userService->getUserById((int)$id);
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        $roles = $this->userService->getAllRoles();

        return view('users/edit', [
            'user' => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Update user
     * POST /users/{id}
     */
    public function update($id)
    {
        $user = $this->userService->getUserById((int)$id);
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        // Build validation rules
        $rules = [
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'full_name' => 'permit_empty|max_length[100]',
            'phone' => 'permit_empty|max_length[20]',
            'status' => 'permit_empty|in_list[active,inactive]',
            'role_id' => 'permit_empty|integer'
        ];

        // Password is optional on update
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'email' => $this->request->getPost('email'),
                'full_name' => $this->request->getPost('full_name'),
                'phone' => $this->request->getPost('phone'),
                'status' => $this->request->getPost('status') ?: 'active',
                'role_id' => $this->request->getPost('role_id') ?: null
            ];

            // Only update password if provided
            if ($this->request->getPost('password')) {
                $data['password'] = $this->request->getPost('password');
            }

            $this->userService->updateUser((int)$id, $data);
            
            return redirect()->to("/users/{$id}")
                ->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete user
     * POST /users/{id}/delete
     */
    public function delete($id)
    {
        try {
            $this->userService->deleteUser((int)$id);
            
            return redirect()->to('/users')
                ->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}

