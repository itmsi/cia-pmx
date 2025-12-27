<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\SprintService;
use App\Services\ProjectService;
use App\Services\IssueService;

class SprintController extends BaseController
{
    protected SprintService $sprintService;
    protected ProjectService $projectService;
    protected IssueService $issueService;

    public function __construct()
    {
        $this->sprintService = new SprintService();
        $this->projectService = new ProjectService();
        $this->issueService = new IssueService();
    }

    /**
     * Display list of sprints for a project
     * GET /sprints?project_id={id}
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $projectId = $this->request->getGet('project_id');

        if (!$projectId) {
            return redirect()->to('/projects')
                ->with('error', 'Project ID is required');
        }

        // Check project access
        if (!$this->projectService->userHasAccess((int)$projectId, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found or access denied');
        }

        $sprints = $this->sprintService->getSprintsByProject((int)$projectId);
        $project = $this->projectService->getProjectById((int)$projectId);

        return view('sprints/index', [
            'sprints' => $sprints,
            'project' => $project
        ]);
    }

    /**
     * Show create sprint form
     * GET /sprints/create?project_id={id}
     */
    public function create()
    {
        $userId = session()->get('user_id');
        $projectId = $this->request->getGet('project_id');

        if (!$projectId) {
            return redirect()->to('/projects')
                ->with('error', 'Project ID is required');
        }

        // Check project access
        if (!$this->projectService->userHasAccess((int)$projectId, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found or access denied');
        }

        $project = $this->projectService->getProjectById((int)$projectId);

        return view('sprints/create', [
            'project' => $project
        ]);
    }

    /**
     * Store new sprint
     * POST /sprints
     */
    public function store()
    {
        $rules = [
            'project_id' => 'required|integer',
            'name' => 'required|min_length[3]|max_length[100]',
            'start_date' => 'required|valid_date',
            'duration_weeks' => 'required|integer|greater_than[0]|less_than[5]',
            'goal' => 'permit_empty|max_length[1000]'
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

            $sprintId = $this->sprintService->createSprint(
                $projectId,
                $this->request->getPost('name'),
                $this->request->getPost('start_date'),
                (int)$this->request->getPost('duration_weeks'),
                [
                    'goal' => $this->request->getPost('goal')
                ]
            );

            return redirect()->to("/sprints?project_id={$projectId}")
                ->with('success', 'Sprint created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show sprint details
     * GET /sprints/{id}
     */
    public function show($id)
    {
        $userId = session()->get('user_id');
        $sprint = $this->sprintService->getSprintById((int)$id);

        if (!$sprint) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Sprint not found');
        }

        // Check project access
        if (!$this->projectService->userHasAccess($sprint['project_id'], $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $project = $this->projectService->getProjectById($sprint['project_id']);
        $issues = $this->sprintService->getIssuesInSprint((int)$id);
        $capacity = $this->sprintService->calculateSprintCapacity((int)$id);

        return view('sprints/show', [
            'sprint' => $sprint,
            'project' => $project,
            'issues' => $issues,
            'capacity' => $capacity
        ]);
    }

    /**
     * Show edit sprint form
     * GET /sprints/{id}/edit
     */
    public function edit($id)
    {
        $userId = session()->get('user_id');
        $sprint = $this->sprintService->getSprintById((int)$id);

        if (!$sprint) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Sprint not found');
        }

        // Check project access
        if (!$this->projectService->userHasAccess($sprint['project_id'], $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $project = $this->projectService->getProjectById($sprint['project_id']);

        return view('sprints/edit', [
            'sprint' => $sprint,
            'project' => $project
        ]);
    }

    /**
     * Update sprint
     * POST /sprints/{id}
     */
    public function update($id)
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'start_date' => 'required|valid_date',
            'duration_weeks' => 'required|integer|greater_than[0]|less_than[5]',
            'goal' => 'permit_empty|max_length[1000]',
            'status' => 'permit_empty|in_list[planned,active,completed]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $sprint = $this->sprintService->getSprintById((int)$id);

            if (!$sprint) {
                throw new \RuntimeException('Sprint not found');
            }

            // Check project access
            if (!$this->projectService->userHasAccess($sprint['project_id'], $userId)) {
                throw new \RuntimeException('You do not have access to this project');
            }

            $this->sprintService->updateSprint((int)$id, [
                'name' => $this->request->getPost('name'),
                'start_date' => $this->request->getPost('start_date'),
                'duration_weeks' => (int)$this->request->getPost('duration_weeks'),
                'goal' => $this->request->getPost('goal'),
                'status' => $this->request->getPost('status') ?? $sprint['status']
            ]);

            return redirect()->to("/sprints/{$id}")
                ->with('success', 'Sprint updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete sprint
     * POST /sprints/{id}/delete
     */
    public function delete($id)
    {
        try {
            $userId = session()->get('user_id');
            $sprint = $this->sprintService->getSprintById((int)$id);

            if (!$sprint) {
                throw new \RuntimeException('Sprint not found');
            }

            // Check project access
            if (!$this->projectService->userHasAccess($sprint['project_id'], $userId)) {
                throw new \RuntimeException('You do not have access to this project');
            }

            $projectId = $sprint['project_id'];
            $this->sprintService->deleteSprint((int)$id);

            return redirect()->to("/sprints?project_id={$projectId}")
                ->with('success', 'Sprint deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Start sprint
     * POST /sprints/{id}/start
     */
    public function start($id)
    {
        try {
            $userId = session()->get('user_id');
            $sprint = $this->sprintService->getSprintById((int)$id);

            if (!$sprint) {
                throw new \RuntimeException('Sprint not found');
            }

            // Check project access
            if (!$this->projectService->userHasAccess($sprint['project_id'], $userId)) {
                throw new \RuntimeException('You do not have access to this project');
            }

            $this->sprintService->startSprint((int)$id);

            return redirect()->back()
                ->with('success', 'Sprint started successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Complete sprint
     * POST /sprints/{id}/complete
     */
    public function complete($id)
    {
        try {
            $userId = session()->get('user_id');
            $sprint = $this->sprintService->getSprintById((int)$id);

            if (!$sprint) {
                throw new \RuntimeException('Sprint not found');
            }

            // Check project access
            if (!$this->projectService->userHasAccess($sprint['project_id'], $userId)) {
                throw new \RuntimeException('You do not have access to this project');
            }

            // Check if auto carry-over is enabled (default: true)
            $autoCarryOver = $this->request->getPost('auto_carry_over') !== '0';

            // Complete sprint (will trigger carry-over automatically if enabled)
            $result = $this->sprintService->completeSprint((int)$id, $autoCarryOver);
            
            if (!$result['success']) {
                throw new \RuntimeException('Failed to complete sprint');
            }
            
            $message = 'Sprint completed successfully';
            
            // Add carry-over info to message
            if ($autoCarryOver && $result['carry_over']['carried_over'] > 0) {
                if ($result['carry_over']['next_sprint_id']) {
                    $message .= ". {$result['carry_over']['carried_over']} unfinished issue(s) carried over to {$result['carry_over']['next_sprint_name']}";
                } else {
                    $message .= ". {$result['carry_over']['carried_over']} unfinished issue(s) moved to backlog";
                }
            }

            return redirect()->back()
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Add issue to sprint
     * POST /sprints/{id}/issues
     */
    public function addIssue($id)
    {
        $rules = [
            'issue_id' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('error', 'Invalid issue ID');
        }

        try {
            $userId = session()->get('user_id');
            $sprint = $this->sprintService->getSprintById((int)$id);

            if (!$sprint) {
                throw new \RuntimeException('Sprint not found');
            }

            // Check project access
            if (!$this->projectService->userHasAccess($sprint['project_id'], $userId)) {
                throw new \RuntimeException('You do not have access to this project');
            }

            $this->sprintService->addIssueToSprint((int)$id, (int)$this->request->getPost('issue_id'));

            return redirect()->back()
                ->with('success', 'Issue added to sprint');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove issue from sprint
     * POST /sprints/{id}/issues/{issueId}/remove
     */
    public function removeIssue($id, $issueId)
    {
        try {
            $userId = session()->get('user_id');
            $sprint = $this->sprintService->getSprintById((int)$id);

            if (!$sprint) {
                throw new \RuntimeException('Sprint not found');
            }

            // Check project access
            if (!$this->projectService->userHasAccess($sprint['project_id'], $userId)) {
                throw new \RuntimeException('You do not have access to this project');
            }

            $this->sprintService->removeIssueFromSprint((int)$issueId);

            return redirect()->back()
                ->with('success', 'Issue removed from sprint');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
