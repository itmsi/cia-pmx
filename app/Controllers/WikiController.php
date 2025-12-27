<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\WikiService;
use App\Services\ProjectService;

class WikiController extends BaseController
{
    protected WikiService $wikiService;
    protected ProjectService $projectService;

    public function __construct()
    {
        $this->wikiService = new WikiService();
        $this->projectService = new ProjectService();
        helper('markdown');
    }

    /**
     * List all wiki pages for a project
     * GET /projects/{projectId}/wiki
     */
    public function index($projectId)
    {
        $userId = session()->get('user_id');

        // Check project access
        if (!$this->projectService->userHasAccess((int)$projectId, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $project = $this->projectService->getProjectById((int)$projectId);
        if (!$project) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found');
        }

        $wikiPages = $this->wikiService->getWikiPagesByProject((int)$projectId, $userId);

        return view('wiki/index', [
            'project' => $project,
            'wikiPages' => $wikiPages
        ]);
    }

    /**
     * Show wiki page
     * GET /projects/{projectId}/wiki/{slug}
     */
    public function show($projectId, $slug)
    {
        $userId = session()->get('user_id');

        // Check project access
        if (!$this->projectService->userHasAccess((int)$projectId, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $project = $this->projectService->getProjectById((int)$projectId);
        if (!$project) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found');
        }

        $wikiPage = $this->wikiService->getWikiPageBySlug((int)$projectId, $slug, $userId);
        if (!$wikiPage) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Wiki page not found');
        }

        // Get versions count
        $versions = $this->wikiService->getWikiVersions($wikiPage['id'], $userId);

        // Check permissions
        $canEdit = $this->wikiService->userCanEditWikiPage($wikiPage['id'], $userId);
        $canDelete = $this->wikiService->userCanDeleteWikiPage($wikiPage['id'], $userId);

        return view('wiki/show', [
            'project' => $project,
            'wikiPage' => $wikiPage,
            'versions' => $versions,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete
        ]);
    }

    /**
     * Show create wiki page form
     * GET /projects/{projectId}/wiki/create
     */
    public function create($projectId)
    {
        $userId = session()->get('user_id');

        // Check project access
        if (!$this->projectService->userHasAccess((int)$projectId, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $project = $this->projectService->getProjectById((int)$projectId);
        if (!$project) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found');
        }

        return view('wiki/create', [
            'project' => $project
        ]);
    }

    /**
     * Store new wiki page
     * POST /projects/{projectId}/wiki
     */
    public function store($projectId)
    {
        $userId = session()->get('user_id');

        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'content' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $title = $this->request->getPost('title');
            $content = $this->request->getPost('content');
            $isPublished = $this->request->getPost('is_published') == '1';

            $wikiId = $this->wikiService->createWikiPage(
                (int)$projectId,
                $userId,
                $title,
                $content,
                $isPublished
            );

            $wikiPage = $this->wikiService->getWikiPageById($wikiId, $userId);

            return redirect()->to("/projects/{$projectId}/wiki/{$wikiPage['slug']}")
                ->with('success', 'Wiki page created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show edit wiki page form
     * GET /projects/{projectId}/wiki/{id}/edit
     */
    public function edit($projectId, $id)
    {
        $userId = session()->get('user_id');

        // Check project access
        if (!$this->projectService->userHasAccess((int)$projectId, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $project = $this->projectService->getProjectById((int)$projectId);
        if (!$project) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found');
        }

        $wikiPage = $this->wikiService->getWikiPageById((int)$id, $userId);
        if (!$wikiPage) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Wiki page not found');
        }

        // Check edit permission
        if (!$this->wikiService->userCanEditWikiPage($wikiPage['id'], $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        return view('wiki/edit', [
            'project' => $project,
            'wikiPage' => $wikiPage
        ]);
    }

    /**
     * Update wiki page
     * POST /projects/{projectId}/wiki/{id}
     */
    public function update($projectId, $id)
    {
        $userId = session()->get('user_id');

        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'content' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $title = $this->request->getPost('title');
            $content = $this->request->getPost('content');
            $changeSummary = $this->request->getPost('change_summary');

            $this->wikiService->updateWikiPage(
                (int)$id,
                $userId,
                $title,
                $content,
                $changeSummary
            );

            $wikiPage = $this->wikiService->getWikiPageById((int)$id, $userId);

            return redirect()->to("/projects/{$projectId}/wiki/{$wikiPage['slug']}")
                ->with('success', 'Wiki page updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete wiki page
     * POST /projects/{projectId}/wiki/{id}/delete
     */
    public function delete($projectId, $id)
    {
        $userId = session()->get('user_id');

        try {
            $this->wikiService->deleteWikiPage((int)$id, $userId);

            return redirect()->to("/projects/{$projectId}/wiki")
                ->with('success', 'Wiki page deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show versions for wiki page
     * GET /projects/{projectId}/wiki/{id}/versions
     */
    public function versions($projectId, $id)
    {
        $userId = session()->get('user_id');

        // Check project access
        if (!$this->projectService->userHasAccess((int)$projectId, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $project = $this->projectService->getProjectById((int)$projectId);
        if (!$project) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found');
        }

        $wikiPage = $this->wikiService->getWikiPageById((int)$id, $userId);
        if (!$wikiPage) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Wiki page not found');
        }

        $versions = $this->wikiService->getWikiVersions((int)$id, $userId);

        return view('wiki/versions', [
            'project' => $project,
            'wikiPage' => $wikiPage,
            'versions' => $versions
        ]);
    }

    /**
     * Show specific version
     * GET /projects/{projectId}/wiki/{id}/versions/{versionNumber}
     */
    public function showVersion($projectId, $id, $versionNumber)
    {
        $userId = session()->get('user_id');

        // Check project access
        if (!$this->projectService->userHasAccess((int)$projectId, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $project = $this->projectService->getProjectById((int)$projectId);
        if (!$project) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found');
        }

        $wikiPage = $this->wikiService->getWikiPageById((int)$id, $userId);
        if (!$wikiPage) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Wiki page not found');
        }

        $version = $this->wikiService->getWikiVersion((int)$id, (int)$versionNumber, $userId);
        if (!$version) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Version not found');
        }

        $canEdit = $this->wikiService->userCanEditWikiPage($wikiPage['id'], $userId);
        
        // Get all versions to determine if this is the latest
        $allVersions = $this->wikiService->getWikiVersions((int)$id, $userId);
        $latestVersionNumber = !empty($allVersions) ? $allVersions[0]['version_number'] : 0;
        $isLatestVersion = $version['version_number'] == $latestVersionNumber;

        return view('wiki/version', [
            'project' => $project,
            'wikiPage' => $wikiPage,
            'version' => $version,
            'canEdit' => $canEdit,
            'isLatestVersion' => $isLatestVersion
        ]);
    }

    /**
     * Restore version
     * POST /projects/{projectId}/wiki/{id}/versions/{versionNumber}/restore
     */
    public function restoreVersion($projectId, $id, $versionNumber)
    {
        $userId = session()->get('user_id');

        try {
            $changeSummary = $this->request->getPost('change_summary');
            
            $this->wikiService->restoreVersion(
                (int)$id,
                (int)$versionNumber,
                $userId,
                $changeSummary
            );

            $wikiPage = $this->wikiService->getWikiPageById((int)$id, $userId);

            return redirect()->to("/projects/{$projectId}/wiki/{$wikiPage['slug']}")
                ->with('success', 'Version restored successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
