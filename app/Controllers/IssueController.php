<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\IssueService;
use App\Services\ProjectService;
use App\Services\LabelService;
use App\Services\AttachmentService;
use App\Services\SavedFilterService;

class IssueController extends BaseController
{
    protected IssueService $issueService;
    protected ProjectService $projectService;
    protected LabelService $labelService;
    protected AttachmentService $attachmentService;
    protected SavedFilterService $savedFilterService;

    public function __construct()
    {
        $this->issueService = new IssueService();
        $this->projectService = new ProjectService();
        $this->labelService = new LabelService();
        $this->attachmentService = new AttachmentService();
        $this->savedFilterService = new SavedFilterService();
    }

    /**
     * Display list of issues
     * GET /issues
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $projectId = $this->request->getGet('project_id');
        
        // Get filter parameters from query string
        $filters = [
            'project_id' => $projectId ? (int)$projectId : null,
            'column_id' => $this->request->getGet('column_id'),
            'priority' => $this->request->getGet('priority'),
            'assignee_id' => $this->request->getGet('assignee_id'),
            'label_id' => $this->request->getGet('label_id'),
            'issue_type' => $this->request->getGet('issue_type'),
            'due_date_from' => $this->request->getGet('due_date_from'),
            'due_date_to' => $this->request->getGet('due_date_to'),
            'due_date_overdue' => $this->request->getGet('due_date_overdue'),
            'search' => $this->request->getGet('search'),
        ];

        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        // Get saved filters for user
        $savedFilters = $this->savedFilterService->getSavedFilters($userId, $projectId);

        // Get issues with filters
        if (!empty($filters)) {
            $issues = $this->issueService->getFilteredIssues($filters, $userId);
        } else {
            // Check for default filter
            $defaultFilter = $this->savedFilterService->getDefaultFilter($userId, $projectId);
            if ($defaultFilter && !empty($defaultFilter['filter_data'])) {
                $filters = array_merge($defaultFilter['filter_data'], ['project_id' => $projectId]);
                $filters = array_filter($filters, function($value) {
                    return $value !== null && $value !== '';
                });
                $issues = $this->issueService->getFilteredIssues($filters, $userId);
            } else {
                // No filters, get all issues
                $issues = $projectId 
                    ? $this->issueService->getIssuesByProject((int)$projectId)
                    : $this->issueService->getIssuesForUser($userId);
            }
        }

        // Get data for filter dropdowns
        $columns = [];
        $labels = [];
        $projectUsers = [];
        
        if ($projectId) {
            $db = \Config\Database::connect();
            $boards = $db->table('boards')
                ->where('project_id', $projectId)
                ->get()
                ->getResultArray();
            
            if (!empty($boards)) {
                $boardIds = array_column($boards, 'id');
                $columns = $db->table('columns')
                    ->whereIn('board_id', $boardIds)
                    ->orderBy('position', 'ASC')
                    ->get()
                    ->getResultArray();
            }
            
            $labels = $this->labelService->getLabelsByProject((int)$projectId);
            $projectUsers = $this->projectService->getProjectUsers((int)$projectId);
        }
        
        return view('issues/index', [
            'issues' => $issues,
            'projectId' => $projectId,
            'filters' => $filters,
            'savedFilters' => $savedFilters,
            'columns' => $columns,
            'labels' => $labels,
            'projectUsers' => $projectUsers
        ]);
    }

    /**
     * Show create issue form
     * GET /issues/create
     */
    public function create()
    {
        $projectId = $this->request->getGet('project_id');
        
        if (!$projectId) {
            throw new \RuntimeException('Project ID is required');
        }

        $project = $this->projectService->getProjectById((int)$projectId);
        if (!$project) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found');
        }

        // Get columns for the project's boards
        $db = \Config\Database::connect();
        $boards = $db->table('boards')
            ->where('project_id', $projectId)
            ->get()
            ->getResultArray();

        $columns = [];
        if (!empty($boards)) {
            $boardIds = array_column($boards, 'id');
            $columns = $db->table('columns')
                ->whereIn('board_id', $boardIds)
                ->orderBy('position', 'ASC')
                ->get()
                ->getResultArray();
        }

        $labels = $this->labelService->getLabelsByProject((int)$projectId);
        $projectUsers = $this->projectService->getProjectUsers((int)$projectId);

        return view('issues/create', [
            'project' => $project,
            'columns' => $columns,
            'labels' => $labels,
            'projectUsers' => $projectUsers
        ]);
    }

    /**
     * Store new issue
     * POST /issues
     */
    public function store()
    {
        $rules = [
            'project_id' => 'required|integer',
            'column_id' => 'required|integer',
            'title' => 'required|min_length[3]|max_length[150]',
            'issue_type' => 'permit_empty|in_list[task,bug,story,epic,sub_task]',
            'priority' => 'permit_empty|in_list[lowest,low,medium,high,critical]',
            'description' => 'permit_empty',
            'assignee_id' => 'permit_empty|integer',
            'due_date' => 'permit_empty|valid_date',
            'estimation' => 'permit_empty|numeric',
            'parent_issue_id' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $projectId = (int)$this->request->getPost('project_id');
            
            // Check project access
            if (!$this->projectService->userHasAccess($projectId, $userId)) {
                throw new \RuntimeException('You do not have access to this project');
            }

            $issueId = $this->issueService->createIssue(
                $projectId,
                (int)$this->request->getPost('column_id'),
                $this->request->getPost('title'),
                $userId,
                [
                    'issue_type' => $this->request->getPost('issue_type') ?? 'task',
                    'priority' => $this->request->getPost('priority') ?? 'medium',
                    'description' => $this->request->getPost('description'),
                    'assignee_id' => $this->request->getPost('assignee_id') ? (int)$this->request->getPost('assignee_id') : null,
                    'due_date' => $this->request->getPost('due_date') ?: null,
                    'estimation' => $this->request->getPost('estimation') ? (float)$this->request->getPost('estimation') : null,
                    'parent_issue_id' => $this->request->getPost('parent_issue_id') ? (int)$this->request->getPost('parent_issue_id') : null,
                ]
            );

            // Add labels if provided
            $labels = $this->request->getPost('labels');
            if (is_array($labels) && !empty($labels)) {
                $this->labelService->setIssueLabels($issueId, $labels);
            }

            return redirect()->to("/projects/{$projectId}")
                ->with('success', 'Issue created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show issue details
     * GET /issues/{id}
     */
    public function show($id)
    {
        $issue = $this->issueService->getIssueById((int)$id);
        
        if (!$issue) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Issue not found');
        }

        // Check project access
        $userId = session()->get('user_id');
        if (!$this->projectService->userHasAccess($issue['project_id'], $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $project = $this->projectService->getProjectById($issue['project_id']);
        $labels = $this->labelService->getIssueLabels((int)$id);
        $subTasks = $this->issueService->getSubTasks((int)$id);

        // Get comments
        $commentService = new \App\Services\CommentService();
        $comments = $commentService->getCommentsByIssue((int)$id);

        // Get attachments
        $attachments = $this->attachmentService->getAttachmentsByIssue((int)$id);

        return view('issues/show', [
            'issue' => $issue,
            'project' => $project,
            'labels' => $labels,
            'subTasks' => $subTasks,
            'comments' => $comments,
            'attachments' => $attachments
        ]);
    }

    /**
     * Show edit issue form
     * GET /issues/{id}/edit
     */
    public function edit($id)
    {
        $issue = $this->issueService->getIssueById((int)$id);
        
        if (!$issue) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Issue not found');
        }

        $userId = session()->get('user_id');
        if (!$this->projectService->userHasAccess($issue['project_id'], $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $project = $this->projectService->getProjectById($issue['project_id']);
        $labels = $this->labelService->getLabelsByProject($issue['project_id']);
        $issueLabels = array_column($this->labelService->getIssueLabels((int)$id), 'id');

        // Get columns
        $db = \Config\Database::connect();
        $boards = $db->table('boards')
            ->where('project_id', $issue['project_id'])
            ->get()
            ->getResultArray();

        $columns = [];
        if (!empty($boards)) {
            $boardIds = array_column($boards, 'id');
            $columns = $db->table('columns')
                ->whereIn('board_id', $boardIds)
                ->orderBy('position', 'ASC')
                ->get()
                ->getResultArray();
        }

        $projectUsers = $this->projectService->getProjectUsers($issue['project_id']);

        return view('issues/edit', [
            'issue' => $issue,
            'project' => $project,
            'labels' => $labels,
            'issueLabels' => $issueLabels,
            'columns' => $columns,
            'projectUsers' => $projectUsers
        ]);
    }

    /**
     * Update issue
     * POST /issues/{id}
     */
    public function update($id)
    {
        $rules = [
            'column_id' => 'required|integer',
            'title' => 'required|min_length[3]|max_length[150]',
            'issue_type' => 'permit_empty|in_list[task,bug,story,epic,sub_task]',
            'priority' => 'permit_empty|in_list[lowest,low,medium,high,critical]',
            'description' => 'permit_empty',
            'assignee_id' => 'permit_empty|integer',
            'due_date' => 'permit_empty|valid_date',
            'estimation' => 'permit_empty|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $issue = $this->issueService->getIssueById((int)$id);
            if (!$issue) {
                throw new \RuntimeException('Issue not found');
            }

            $data = [
                'column_id' => (int)$this->request->getPost('column_id'),
                'title' => $this->request->getPost('title'),
                'issue_type' => $this->request->getPost('issue_type'),
                'priority' => $this->request->getPost('priority'),
                'description' => $this->request->getPost('description'),
                'assignee_id' => $this->request->getPost('assignee_id') ? (int)$this->request->getPost('assignee_id') : null,
                'due_date' => $this->request->getPost('due_date') ?: null,
                'estimation' => $this->request->getPost('estimation') ? (float)$this->request->getPost('estimation') : null,
            ];

            $this->issueService->updateIssue((int)$id, $data);

            // Update labels
            $labels = $this->request->getPost('labels') ?? [];
            $this->labelService->setIssueLabels((int)$id, $labels);

            return redirect()->to("/issues/{$id}")
                ->with('success', 'Issue updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete issue
     * POST /issues/{id}/delete
     */
    public function delete($id)
    {
        try {
            $issue = $this->issueService->getIssueById((int)$id);
            if (!$issue) {
                throw new \RuntimeException('Issue not found');
            }

            $projectId = $issue['project_id'];
            $this->issueService->deleteIssue((int)$id);
            
            return redirect()->to("/projects/{$projectId}")
                ->with('success', 'Issue deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Move issue (drag & drop)
     * POST /issues/{id}/move
     */
    public function move($id)
    {
        $rules = [
            'column_id' => 'required|integer',
            'position' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid data'
            ])->setStatusCode(400);
        }

        try {
            $this->issueService->moveIssue(
                (int)$id,
                (int)$this->request->getPost('column_id'),
                (int)$this->request->getPost('position')
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Issue moved successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(400);
        }
    }

    /**
     * Assign issue to user
     * POST /issues/{id}/assign
     */
    public function assign($id)
    {
        $rules = [
            'assignee_id' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('error', 'Invalid assignee');
        }

        try {
            $assigneeId = $this->request->getPost('assignee_id') 
                ? (int)$this->request->getPost('assignee_id') 
                : null;

            $this->issueService->assignIssue((int)$id, $assigneeId);

            return redirect()->back()
                ->with('success', 'Issue assigned successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Save filter
     * POST /issues/filters/save
     */
    public function saveFilter()
    {
        $rules = [
            'name' => 'required|min_length[1]|max_length[100]',
            'filter_data' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $name = $this->request->getPost('name');
            $projectId = $this->request->getPost('project_id') ? (int)$this->request->getPost('project_id') : null;
            $isDefault = $this->request->getPost('is_default') ? true : false;

            // Get filter data from form
            $filterData = [
                'column_id' => $this->request->getPost('column_id'),
                'priority' => $this->request->getPost('priority'),
                'assignee_id' => $this->request->getPost('assignee_id'),
                'label_id' => $this->request->getPost('label_id'),
                'issue_type' => $this->request->getPost('issue_type'),
                'due_date_from' => $this->request->getPost('due_date_from'),
                'due_date_to' => $this->request->getPost('due_date_to'),
                'due_date_overdue' => $this->request->getPost('due_date_overdue'),
                'search' => $this->request->getPost('search'),
            ];

            // Remove empty values
            $filterData = array_filter($filterData, function($value) {
                return $value !== null && $value !== '';
            });

            $this->savedFilterService->saveFilter($userId, $name, $filterData, $projectId, $isDefault);

            return redirect()->back()
                ->with('success', 'Filter saved successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Load saved filter
     * GET /issues/filters/load/{id}
     */
    public function loadFilter($id)
    {
        try {
            $userId = session()->get('user_id');
            $filter = $this->savedFilterService->getSavedFilterById((int)$id, $userId);

            if (!$filter) {
                throw new \RuntimeException('Filter not found');
            }

            $filterData = $filter['filter_data'] ?? [];
            $projectId = $filter['project_id'] ?? null;

            // Build query string
            $queryParams = [];
            if ($projectId) {
                $queryParams['project_id'] = $projectId;
            }
            foreach ($filterData as $key => $value) {
                if ($value !== null && $value !== '') {
                    if (is_array($value)) {
                        foreach ($value as $v) {
                            $queryParams[$key . '[]'] = $v;
                        }
                    } else {
                        $queryParams[$key] = $value;
                    }
                }
            }

            $url = '/issues';
            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }

            return redirect()->to($url);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete saved filter
     * POST /issues/filters/delete/{id}
     */
    public function deleteFilter($id)
    {
        try {
            $userId = session()->get('user_id');
            $this->savedFilterService->deleteFilter((int)$id, $userId);

            return redirect()->back()
                ->with('success', 'Filter deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}

