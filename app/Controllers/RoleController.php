<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\RoleService;
use App\Services\PermissionService;

class RoleController extends BaseController
{
    protected RoleService $roleService;
    protected PermissionService $permissionService;

    public function __construct()
    {
        $this->roleService = new RoleService();
        $this->permissionService = new PermissionService();
    }

    /**
     * Display list of roles
     * GET /roles
     */
    public function index()
    {
        $roles = $this->roleService->getAllRoles();
        
        return view('roles/index', [
            'roles' => $roles
        ]);
    }

    /**
     * Show create role form
     * GET /roles/create
     */
    public function create()
    {
        $permissions = $this->permissionService->getAllPermissions();
        
        return view('roles/create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store new role
     * POST /roles
     */
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[50]',
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $roleId = $this->roleService->createRole(
                $this->request->getPost('name'),
                $this->request->getPost('description')
            );

            // Assign permissions if provided
            $permissions = $this->request->getPost('permissions');
            if (is_array($permissions)) {
                foreach ($permissions as $permissionId) {
                    $this->roleService->assignPermission($roleId, (int)$permissionId);
                }
            }

            return redirect()->to('/roles')
                ->with('success', 'Role created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show role details
     * GET /roles/{id}
     */
    public function show($id)
    {
        $role = $this->roleService->getRoleById((int)$id);
        
        if (!$role) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Role not found');
        }

        $permissions = $this->roleService->getRolePermissions((int)$id);
        $allPermissions = $this->permissionService->getAllPermissions();

        return view('roles/show', [
            'role' => $role,
            'permissions' => $permissions,
            'allPermissions' => $allPermissions
        ]);
    }

    /**
     * Show edit role form
     * GET /roles/{id}/edit
     */
    public function edit($id)
    {
        $role = $this->roleService->getRoleById((int)$id);
        
        if (!$role) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Role not found');
        }

        $permissions = $this->roleService->getRolePermissions((int)$id);
        $allPermissions = $this->permissionService->getAllPermissions();

        return view('roles/edit', [
            'role' => $role,
            'permissions' => $permissions,
            'allPermissions' => $allPermissions
        ]);
    }

    /**
     * Update role
     * POST /roles/{id}
     */
    public function update($id)
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[50]',
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $this->roleService->updateRole(
                (int)$id,
                $this->request->getPost('name'),
                $this->request->getPost('description')
            );

            // Update permissions
            $permissions = $this->request->getPost('permissions') ?? [];
            $currentPermissions = array_column($this->roleService->getRolePermissions((int)$id), 'id');

            // Remove permissions
            foreach ($currentPermissions as $permissionId) {
                if (!in_array($permissionId, $permissions)) {
                    $this->roleService->removePermission((int)$id, $permissionId);
                }
            }

            // Add new permissions
            foreach ($permissions as $permissionId) {
                if (!in_array($permissionId, $currentPermissions)) {
                    $this->roleService->assignPermission((int)$id, (int)$permissionId);
                }
            }

            return redirect()->to('/roles')
                ->with('success', 'Role updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete role
     * POST /roles/{id}/delete
     */
    public function delete($id)
    {
        try {
            $this->roleService->deleteRole((int)$id);
            
            return redirect()->to('/roles')
                ->with('success', 'Role deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}

