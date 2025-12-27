<?php

namespace App\Services;

use App\Models\WikiModel;
use App\Models\WikiVersionModel;
use App\Models\WikiPermissionModel;
use App\Services\ActivityLogService;
use App\Services\ProjectService;

class WikiService
{
    protected WikiModel $wikiModel;
    protected WikiVersionModel $wikiVersionModel;
    protected WikiPermissionModel $wikiPermissionModel;
    protected ActivityLogService $logService;
    protected ProjectService $projectService;
    protected $db;

    public function __construct()
    {
        $this->wikiModel = new WikiModel();
        $this->wikiVersionModel = new WikiVersionModel();
        $this->wikiPermissionModel = new WikiPermissionModel();
        $this->logService = new ActivityLogService();
        $this->projectService = new ProjectService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new wiki page
     */
    public function createWikiPage(int $projectId, int $userId, string $title, string $content, bool $isPublished = true): int
    {
        // Check project access
        if (!$this->projectService->userHasAccess($projectId, $userId)) {
            throw new \RuntimeException('You do not have access to this project');
        }

        // Generate unique slug for project
        $slug = $this->generateSlug($projectId, $title);

        $wikiId = $this->wikiModel->insert([
            'project_id' => $projectId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'created_by' => $userId,
            'updated_by' => $userId,
            'is_published' => $isPublished
        ]);

        // Save initial version
        $this->saveVersion($wikiId, $userId, $title, $content, 1, 'Initial version');

        $this->logService->log(
            'create',
            'wiki_page',
            $wikiId,
            "Wiki page created: {$title}"
        );

        return $wikiId;
    }

    /**
     * Get all wiki pages for a project
     */
    public function getWikiPagesByProject(int $projectId, int $userId): array
    {
        // Check project access
        if (!$this->projectService->userHasAccess($projectId, $userId)) {
            throw new \RuntimeException('You do not have access to this project');
        }

        $pages = $this->wikiModel
            ->select('wiki_pages.*, users.full_name as creator_name, users.email as creator_email')
            ->join('users', 'users.id = wiki_pages.created_by')
            ->where('wiki_pages.project_id', $projectId)
            ->where('wiki_pages.is_published', true)
            ->orderBy('wiki_pages.created_at', 'DESC')
            ->findAll();

        // Filter by permissions
        $filtered = [];
        foreach ($pages as $page) {
            if ($this->userCanViewWikiPage($page['id'], $userId)) {
                $filtered[] = $page;
            }
        }

        return $filtered;
    }

    /**
     * Get wiki page by ID
     */
    public function getWikiPageById(int $wikiId, int $userId): ?array
    {
        $page = $this->wikiModel
            ->select('wiki_pages.*, users.full_name as creator_name, users.email as creator_email, updater.full_name as updater_name, updater.email as updater_email')
            ->join('users', 'users.id = wiki_pages.created_by')
            ->join('users as updater', 'updater.id = wiki_pages.updated_by', 'left')
            ->where('wiki_pages.id', $wikiId)
            ->first();

        if (!$page) {
            return null;
        }

        // Check access
        if (!$this->userCanViewWikiPage($wikiId, $userId)) {
            throw new \RuntimeException('You do not have permission to view this wiki page');
        }

        return $page;
    }

    /**
     * Get wiki page by slug
     */
    public function getWikiPageBySlug(int $projectId, string $slug, int $userId): ?array
    {
        $page = $this->wikiModel
            ->select('wiki_pages.*, users.full_name as creator_name, users.email as creator_email, updater.full_name as updater_name, updater.email as updater_email')
            ->join('users', 'users.id = wiki_pages.created_by')
            ->join('users as updater', 'updater.id = wiki_pages.updated_by', 'left')
            ->where('wiki_pages.project_id', $projectId)
            ->where('wiki_pages.slug', $slug)
            ->first();

        if (!$page) {
            return null;
        }

        // Check access
        if (!$this->userCanViewWikiPage($page['id'], $userId)) {
            throw new \RuntimeException('You do not have permission to view this wiki page');
        }

        return $page;
    }

    /**
     * Update wiki page
     */
    public function updateWikiPage(int $wikiId, int $userId, string $title, string $content, ?string $changeSummary = null): bool
    {
        $page = $this->wikiModel->find($wikiId);
        if (!$page) {
            throw new \RuntimeException('Wiki page not found');
        }

        // Check edit permission
        if (!$this->userCanEditWikiPage($wikiId, $userId)) {
            throw new \RuntimeException('You do not have permission to edit this wiki page');
        }

        // Get next version number
        $lastVersion = $this->wikiVersionModel
            ->where('wiki_page_id', $wikiId)
            ->orderBy('version_number', 'DESC')
            ->first();

        $nextVersion = $lastVersion ? $lastVersion['version_number'] + 1 : 1;

        // Save current state as version before update
        $this->saveVersion($wikiId, $userId, $page['title'], $page['content'], $nextVersion, $changeSummary);

        // Generate new slug if title changed
        $slug = $page['slug'];
        if ($title !== $page['title']) {
            $slug = $this->generateSlug($page['project_id'], $title, $wikiId);
        }

        $result = $this->wikiModel->update($wikiId, [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'updated_by' => $userId
        ]);

        if ($result) {
            $this->logService->log(
                'update',
                'wiki_page',
                $wikiId,
                "Wiki page updated: {$title}"
            );
        }

        return $result;
    }

    /**
     * Delete wiki page
     */
    public function deleteWikiPage(int $wikiId, int $userId): bool
    {
        $page = $this->wikiModel->find($wikiId);
        if (!$page) {
            throw new \RuntimeException('Wiki page not found');
        }

        // Check delete permission
        if (!$this->userCanDeleteWikiPage($wikiId, $userId)) {
            throw new \RuntimeException('You do not have permission to delete this wiki page');
        }

        // Delete versions
        $this->wikiVersionModel->where('wiki_page_id', $wikiId)->delete();

        // Delete permissions
        $this->wikiPermissionModel->where('wiki_page_id', $wikiId)->delete();

        $result = $this->wikiModel->delete($wikiId);

        if ($result) {
            $this->logService->log(
                'delete',
                'wiki_page',
                $wikiId,
                "Wiki page deleted: {$page['title']}"
            );
        }

        return $result;
    }

    /**
     * Save version
     */
    protected function saveVersion(int $wikiPageId, int $userId, string $title, string $content, int $versionNumber, ?string $changeSummary = null): int
    {
        return $this->wikiVersionModel->insert([
            'wiki_page_id' => $wikiPageId,
            'version_number' => $versionNumber,
            'title' => $title,
            'content' => $content,
            'created_by' => $userId,
            'change_summary' => $changeSummary
        ]);
    }

    /**
     * Get versions for wiki page
     */
    public function getWikiVersions(int $wikiId, int $userId): array
    {
        // Check view permission
        if (!$this->userCanViewWikiPage($wikiId, $userId)) {
            throw new \RuntimeException('You do not have permission to view this wiki page');
        }

        return $this->wikiVersionModel
            ->select('wiki_versions.*, users.full_name as creator_name, users.email as creator_email')
            ->join('users', 'users.id = wiki_versions.created_by')
            ->where('wiki_versions.wiki_page_id', $wikiId)
            ->orderBy('wiki_versions.version_number', 'DESC')
            ->findAll();
    }

    /**
     * Get specific version
     */
    public function getWikiVersion(int $wikiId, int $versionNumber, int $userId): ?array
    {
        // Check view permission
        if (!$this->userCanViewWikiPage($wikiId, $userId)) {
            throw new \RuntimeException('You do not have permission to view this wiki page');
        }

        return $this->wikiVersionModel
            ->select('wiki_versions.*, users.full_name as creator_name, users.email as creator_email')
            ->join('users', 'users.id = wiki_versions.created_by')
            ->where('wiki_versions.wiki_page_id', $wikiId)
            ->where('wiki_versions.version_number', $versionNumber)
            ->first();
    }

    /**
     * Restore version
     */
    public function restoreVersion(int $wikiId, int $versionNumber, int $userId, ?string $changeSummary = null): bool
    {
        // Check edit permission
        if (!$this->userCanEditWikiPage($wikiId, $userId)) {
            throw new \RuntimeException('You do not have permission to edit this wiki page');
        }

        $version = $this->getWikiVersion($wikiId, $versionNumber, $userId);
        if (!$version) {
            throw new \RuntimeException('Version not found');
        }

        return $this->updateWikiPage($wikiId, $userId, $version['title'], $version['content'], $changeSummary ?? "Restored from version {$versionNumber}");
    }

    /**
     * Check if user can view wiki page
     */
    public function userCanViewWikiPage(int $wikiId, int $userId): bool
    {
        $page = $this->wikiModel->find($wikiId);
        if (!$page) {
            return false;
        }

        // Check project access first
        if (!$this->projectService->userHasAccess($page['project_id'], $userId)) {
            return false;
        }

        // Project owner always has access
        $project = $this->projectService->getProjectById($page['project_id']);
        if ($project && $project['owner_id'] == $userId) {
            return true;
        }

        // Page creator always has access
        if ($page['created_by'] == $userId) {
            return true;
        }

        // Check page-level permission
        $pagePermission = $this->wikiPermissionModel
            ->where('wiki_page_id', $wikiId)
            ->where('user_id', $userId)
            ->where('can_view', true)
            ->first();

        if ($pagePermission) {
            return true;
        }

        // Check project-level permission
        $projectPermission = $this->wikiPermissionModel
            ->where('project_id', $page['project_id'])
            ->where('wiki_page_id IS NULL')
            ->where('user_id', $userId)
            ->where('can_view', true)
            ->first();

        return $projectPermission !== null;
    }

    /**
     * Check if user can edit wiki page
     */
    public function userCanEditWikiPage(int $wikiId, int $userId): bool
    {
        $page = $this->wikiModel->find($wikiId);
        if (!$page) {
            return false;
        }

        // Project owner always has edit access
        $project = $this->projectService->getProjectById($page['project_id']);
        if ($project && $project['owner_id'] == $userId) {
            return true;
        }

        // Page creator always has edit access
        if ($page['created_by'] == $userId) {
            return true;
        }

        // Check page-level permission
        $pagePermission = $this->wikiPermissionModel
            ->where('wiki_page_id', $wikiId)
            ->where('user_id', $userId)
            ->where('can_edit', true)
            ->first();

        if ($pagePermission) {
            return true;
        }

        // Check project-level permission
        $projectPermission = $this->wikiPermissionModel
            ->where('project_id', $page['project_id'])
            ->where('wiki_page_id IS NULL')
            ->where('user_id', $userId)
            ->where('can_edit', true)
            ->first();

        return $projectPermission !== null;
    }

    /**
     * Check if user can delete wiki page
     */
    public function userCanDeleteWikiPage(int $wikiId, int $userId): bool
    {
        $page = $this->wikiModel->find($wikiId);
        if (!$page) {
            return false;
        }

        // Project owner always has delete access
        $project = $this->projectService->getProjectById($page['project_id']);
        if ($project && $project['owner_id'] == $userId) {
            return true;
        }

        // Page creator always has delete access
        if ($page['created_by'] == $userId) {
            return true;
        }

        // Check page-level permission
        $pagePermission = $this->wikiPermissionModel
            ->where('wiki_page_id', $wikiId)
            ->where('user_id', $userId)
            ->where('can_delete', true)
            ->first();

        if ($pagePermission) {
            return true;
        }

        // Check project-level permission
        $projectPermission = $this->wikiPermissionModel
            ->where('project_id', $page['project_id'])
            ->where('wiki_page_id IS NULL')
            ->where('user_id', $userId)
            ->where('can_delete', true)
            ->first();

        return $projectPermission !== null;
    }

    /**
     * Add permission for wiki page
     */
    public function addWikiPermission(int $wikiPageId, ?int $projectId, int $userId, bool $canView = false, bool $canEdit = false, bool $canDelete = false): int
    {
        // Only one of wiki_page_id or project_id should be set
        if ($wikiPageId && $projectId) {
            throw new \RuntimeException('Cannot set both wiki_page_id and project_id');
        }

        return $this->wikiPermissionModel->insert([
            'wiki_page_id' => $wikiPageId,
            'project_id' => $projectId,
            'user_id' => $userId,
            'can_view' => $canView,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete
        ]);
    }

    /**
     * Get permissions for wiki page or project
     */
    public function getWikiPermissions(?int $wikiPageId = null, ?int $projectId = null): array
    {
        $builder = $this->wikiPermissionModel
            ->select('wiki_permissions.*, users.full_name as user_name, users.email as user_email')
            ->join('users', 'users.id = wiki_permissions.user_id');

        if ($wikiPageId) {
            $builder->where('wiki_permissions.wiki_page_id', $wikiPageId);
        } elseif ($projectId) {
            $builder->where('wiki_permissions.project_id', $projectId)
                    ->where('wiki_permissions.wiki_page_id IS NULL');
        }

        return $builder->findAll();
    }

    /**
     * Remove permission
     */
    public function removeWikiPermission(int $permissionId): bool
    {
        return $this->wikiPermissionModel->delete($permissionId);
    }

    /**
     * Generate unique slug for project
     */
    protected function generateSlug(int $projectId, string $title, ?int $excludeWikiId = null): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure uniqueness within project
        $baseSlug = $slug;
        $counter = 1;
        
        $builder = $this->wikiModel->where('project_id', $projectId)->where('slug', $slug);
        if ($excludeWikiId) {
            $builder->where('id !=', $excludeWikiId);
        }
        
        while ($builder->first()) {
            $slug = $baseSlug . '-' . $counter;
            $builder = $this->wikiModel->where('project_id', $projectId)->where('slug', $slug);
            if ($excludeWikiId) {
                $builder->where('id !=', $excludeWikiId);
            }
            $counter++;
        }

        return $slug;
    }
}
