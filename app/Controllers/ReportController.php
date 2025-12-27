<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ReportService;
use App\Services\ProjectService;
use App\Services\SprintService;

class ReportController extends BaseController
{
    protected ReportService $reportService;
    protected ProjectService $projectService;
    protected SprintService $sprintService;

    public function __construct()
    {
        $this->reportService = new ReportService();
        $this->projectService = new ProjectService();
        $this->sprintService = new SprintService();
    }

    /**
     * Show reports index
     * GET /projects/{projectId}/reports
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

        // Get sprints for dropdown
        $sprints = $this->sprintService->getSprintsByProject((int)$projectId);

        return view('reports/index', [
            'project' => $project,
            'sprints' => $sprints
        ]);
    }

    /**
     * Get burndown chart data
     * GET /projects/{projectId}/reports/burndown/{sprintId}
     */
    public function burndown($projectId, $sprintId)
    {
        $userId = session()->get('user_id');

        try {
            $data = $this->reportService->getBurndownChart((int)$sprintId, $userId);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(400);
        }
    }

    /**
     * Get burnup chart data
     * GET /projects/{projectId}/reports/burnup/{sprintId}
     */
    public function burnup($projectId, $sprintId)
    {
        $userId = session()->get('user_id');

        try {
            $data = $this->reportService->getBurnupChart((int)$sprintId, $userId);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(400);
        }
    }

    /**
     * Get velocity chart data
     * GET /projects/{projectId}/reports/velocity
     */
    public function velocity($projectId)
    {
        $userId = session()->get('user_id');

        try {
            $data = $this->reportService->getVelocityChart((int)$projectId, $userId);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(400);
        }
    }

    /**
     * Get lead time and cycle time
     * GET /projects/{projectId}/reports/lead-time
     */
    public function leadTime($projectId)
    {
        $userId = session()->get('user_id');

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        try {
            $data = $this->reportService->getLeadTimeAndCycleTime((int)$projectId, $userId, $startDate, $endDate);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(400);
        }
    }

    /**
     * Get productivity per user
     * GET /projects/{projectId}/reports/productivity
     */
    public function productivity($projectId)
    {
        $userId = session()->get('user_id');

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        try {
            $data = $this->reportService->getProductivityPerUser((int)$projectId, $userId, $startDate, $endDate);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(400);
        }
    }
}
