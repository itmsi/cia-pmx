<?php

namespace App\Services;

use App\Models\IssueModel;
use App\Models\ProjectModel;

class DashboardService
{
    protected IssueModel $issueModel;
    protected ProjectModel $projectModel;
    protected ProjectService $projectService;
    protected $db;

    public function __construct()
    {
        $this->issueModel = new IssueModel();
        $this->projectModel = new ProjectModel();
        $this->projectService = new ProjectService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get total projects count for user
     */
    public function getTotalProjects(int $userId): int
    {
        $projects = $this->projectService->getProjectsForUser($userId);
        return count($projects);
    }

    /**
     * Get tasks count by status for user
     */
    public function getTasksByStatus(int $userId): array
    {
        // Get all projects user has access to
        $projects = $this->projectService->getProjectsForUser($userId);
        $projectIds = array_column($projects, 'id');

        if (empty($projectIds)) {
            return [];
        }

        // Get issues grouped by column (status)
        $issues = $this->db->table('issues')
            ->select('columns.name as status, COUNT(issues.id) as count')
            ->join('columns', 'columns.id = issues.column_id')
            ->whereIn('issues.project_id', $projectIds)
            ->groupBy('columns.id', 'columns.name', 'columns.position')
            ->orderBy('columns.position', 'ASC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($issues as $issue) {
            $result[$issue['status']] = (int)$issue['count'];
        }

        return $result;
    }

    /**
     * Get overdue tasks count for user
     */
    public function getOverdueTasksCount(int $userId): int
    {
        // Get all projects user has access to
        $projects = $this->projectService->getProjectsForUser($userId);
        $projectIds = array_column($projects, 'id');

        if (empty($projectIds)) {
            return 0;
        }

        // Get columns that are not "Done" status
        $doneColumns = $this->db->table('columns')
            ->select('id')
            ->like('name', 'done', 'both')
            ->get()
            ->getResultArray();
        $doneColumnIds = array_column($doneColumns, 'id');

        $builder = $this->db->table('issues')
            ->where('due_date <', date('Y-m-d'))
            ->where('due_date IS NOT NULL', null, false)
            ->whereIn('project_id', $projectIds);

        if (!empty($doneColumnIds)) {
            $builder->whereNotIn('column_id', $doneColumnIds);
        }

        return $builder->countAllResults();
    }

    /**
     * Get overdue tasks details for user
     */
    public function getOverdueTasks(int $userId, int $limit = 10): array
    {
        // Get all projects user has access to
        $projects = $this->projectService->getProjectsForUser($userId);
        $projectIds = array_column($projects, 'id');

        if (empty($projectIds)) {
            return [];
        }

        // Get columns that are not "Done" status
        $doneColumns = $this->db->table('columns')
            ->select('id')
            ->like('name', 'done', 'both')
            ->get()
            ->getResultArray();
        $doneColumnIds = array_column($doneColumns, 'id');

        $builder = $this->db->table('issues')
            ->select('issues.*, projects.name as project_name, projects.key as project_key, columns.name as column_name, users.full_name as assignee_name, users.email as assignee_email')
            ->join('projects', 'projects.id = issues.project_id')
            ->join('columns', 'columns.id = issues.column_id', 'left')
            ->join('users', 'users.id = issues.assignee_id', 'left')
            ->where('issues.due_date <', date('Y-m-d'))
            ->where('issues.due_date IS NOT NULL', null, false)
            ->whereIn('issues.project_id', $projectIds)
            ->orderBy('issues.due_date', 'ASC')
            ->limit($limit);

        if (!empty($doneColumnIds)) {
            $builder->whereNotIn('issues.column_id', $doneColumnIds);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get tasks count by assignee for user
     */
    public function getTasksByAssignee(int $userId): array
    {
        // Get all projects user has access to
        $projects = $this->projectService->getProjectsForUser($userId);
        $projectIds = array_column($projects, 'id');

        if (empty($projectIds)) {
            return [];
        }

        // Get issues grouped by assignee
        $issues = $this->db->table('issues')
            ->select('users.id as user_id, users.full_name as assignee_name, users.email as assignee_email, COUNT(issues.id) as count')
            ->join('users', 'users.id = issues.assignee_id', 'left')
            ->whereIn('issues.project_id', $projectIds)
            ->where('issues.assignee_id IS NOT NULL', null, false)
            ->groupBy('users.id', 'users.full_name', 'users.email')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($issues as $issue) {
            $assigneeName = $issue['assignee_name'] ?? $issue['assignee_email'] ?? 'Unassigned';
            if (!$issue['user_id']) {
                $assigneeName = 'Unassigned';
            }
            $result[] = [
                'assignee_id' => $issue['user_id'],
                'assignee_name' => $assigneeName,
                'count' => (int)$issue['count']
            ];
        }

        // Also count unassigned
        $unassignedCount = $this->issueModel
            ->whereIn('project_id', $projectIds)
            ->where('assignee_id IS NULL')
            ->countAllResults();

        if ($unassignedCount > 0) {
            $result[] = [
                'assignee_id' => null,
                'assignee_name' => 'Unassigned',
                'count' => $unassignedCount
            ];
        }

        return $result;
    }

    /**
     * Get progress percentage for user's projects
     */
    public function getProgressPercentage(int $userId): array
    {
        // Get all projects user has access to
        $projects = $this->projectService->getProjectsForUser($userId);
        $projectIds = array_column($projects, 'id');

        if (empty($projectIds)) {
            return [
                'overall' => 0,
                'by_project' => []
            ];
        }

        // Get columns that are considered "Done"
        $doneColumns = $this->db->table('columns')
            ->select('id')
            ->like('name', 'done', 'both')
            ->get()
            ->getResultArray();
        $doneColumnIds = array_column($doneColumns, 'id');

        $result = [
            'overall' => 0,
            'by_project' => []
        ];

        $totalTasks = 0;
        $completedTasks = 0;

        foreach ($projects as $project) {
            $projectTotalTasks = $this->issueModel
                ->where('project_id', $project['id'])
                ->countAllResults();

            $projectCompletedTasks = 0;
            if (!empty($doneColumnIds)) {
                $projectCompletedTasks = $this->issueModel
                    ->where('project_id', $project['id'])
                    ->whereIn('column_id', $doneColumnIds)
                    ->countAllResults();
            }

            $projectProgress = $projectTotalTasks > 0 
                ? round(($projectCompletedTasks / $projectTotalTasks) * 100, 1)
                : 0;

            $result['by_project'][] = [
                'project_id' => $project['id'],
                'project_name' => $project['name'],
                'project_key' => $project['key'] ?? '',
                'total_tasks' => $projectTotalTasks,
                'completed_tasks' => $projectCompletedTasks,
                'progress' => $projectProgress
            ];

            $totalTasks += $projectTotalTasks;
            $completedTasks += $projectCompletedTasks;
        }

        $result['overall'] = $totalTasks > 0 
            ? round(($completedTasks / $totalTasks) * 100, 1)
            : 0;

        return $result;
    }

    /**
     * Get all dashboard data for user
     */
    public function getDashboardData(int $userId): array
    {
        return [
            'total_projects' => $this->getTotalProjects($userId),
            'tasks_by_status' => $this->getTasksByStatus($userId),
            'overdue_tasks_count' => $this->getOverdueTasksCount($userId),
            'overdue_tasks' => $this->getOverdueTasks($userId, 5),
            'tasks_by_assignee' => $this->getTasksByAssignee($userId),
            'progress' => $this->getProgressPercentage($userId)
        ];
    }
}
