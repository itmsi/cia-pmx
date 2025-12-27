<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\PermissionService;

class PermissionController extends BaseController
{
    protected PermissionService $permissionService;

    public function __construct()
    {
        $this->permissionService = new PermissionService();
    }

    /**
     * Display list of permissions
     * GET /permissions
     */
    public function index()
    {
        $permissions = $this->permissionService->getAllPermissions();
        
        return view('permissions/index', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Show create permission form
     * GET /permissions/create
     */
    public function create()
    {
        return view('permissions/create');
    }

    /**
     * Store new permission
     * POST /permissions
     */
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $this->permissionService->createPermission(
                $this->request->getPost('name'),
                $this->request->getPost('description')
            );

            return redirect()->to('/permissions')
                ->with('success', 'Permission created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show permission details
     * GET /permissions/{id}
     */
    public function show($id)
    {
        $permission = $this->permissionService->getPermissionById((int)$id);
        
        if (!$permission) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Permission not found');
        }

        return view('permissions/show', [
            'permission' => $permission
        ]);
    }

    /**
     * Show edit permission form
     * GET /permissions/{id}/edit
     */
    public function edit($id)
    {
        $permission = $this->permissionService->getPermissionById((int)$id);
        
        if (!$permission) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Permission not found');
        }

        return view('permissions/edit', [
            'permission' => $permission
        ]);
    }

    /**
     * Update permission
     * POST /permissions/{id}
     */
    public function update($id)
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $this->permissionService->updatePermission(
                (int)$id,
                $this->request->getPost('name'),
                $this->request->getPost('description')
            );

            return redirect()->to('/permissions')
                ->with('success', 'Permission updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete permission
     * POST /permissions/{id}/delete
     */
    public function delete($id)
    {
        try {
            $this->permissionService->deletePermission((int)$id);
            
            return redirect()->to('/permissions')
                ->with('success', 'Permission deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}

