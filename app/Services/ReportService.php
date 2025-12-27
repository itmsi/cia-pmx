<?php

namespace App\Services;

use App\Models\IssueModel;
use App\Models\SprintModel;
use App\Services\ProjectService;
use App\Services\ActivityLogService;

class ReportService
{
    protected IssueModel $issueModel;
    protected SprintModel $sprintModel;
    protected ProjectService $projectService;
    protected ActivityLogService $activityLogService;
    protected $db;

    public function __construct()
    {
        $this->issueModel = new IssueModel();
        $this->sprintModel = new SprintModel();
        $this->projectService = new ProjectService();
        $this->activityLogService = new ActivityLogService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get burndown chart data for a sprint
     * Returns remaining work (story points) per day
     */
    public function getBurndownChart(int $sprintId, int $userId): array
    {
        $sprint = $this->sprintModel->find($sprintId);
        if (!$sprint) {
            throw new \RuntimeException('Sprint not found');
        }

        // Check project access
        if (!$this->projectService->userHasAccess($sprint['project_id'], $userId)) {
            throw new \RuntimeException('Access denied');
        }

        // Get all issues in sprint
        $issues = $this->db->table('issues')
            ->where('sprint_id', $sprintId)
            ->get()
            ->getResultArray();

        // Get Done column ID
        $doneColumns = $this->getDoneColumns($sprint['project_id']);

        // Calculate total story points
        $totalPoints = 0;
        foreach ($issues as $issue) {
            $totalPoints += (float)($issue['estimation'] ?? 0);
        }

        // Generate date range
        $startDate = new \DateTime($sprint['start_date']);
        $endDate = new \DateTime($sprint['end_date']);
        $currentDate = clone $startDate;

        $chartData = [];
        
        // Get completion dates from activity logs
        $completionDates = $this->getCompletionDates($issues, $doneColumns);

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            
            // Calculate remaining points on this date (cumulative completed up to this date)
            $completedPoints = 0;
            foreach ($issues as $issue) {
                $issueId = $issue['id'];
                $completedDate = $completionDates[$issueId] ?? null;
                
                // If issue was completed on or before this date
                if ($completedDate && $completedDate <= $dateStr) {
                    $completedPoints += (float)($issue['estimation'] ?? 0);
                }
            }

            $remainingPoints = $totalPoints - $completedPoints;
            if ($remainingPoints < 0) $remainingPoints = 0;

            $chartData[] = [
                'date' => $dateStr,
                'remaining' => round($remainingPoints, 1),
                'ideal' => $this->calculateIdealBurndown($totalPoints, $startDate, $endDate, $currentDate)
            ];

            $currentDate->modify('+1 day');
        }

        return [
            'sprint' => $sprint,
            'total_points' => $totalPoints,
            'chart_data' => $chartData
        ];
    }

    /**
     * Get burnup chart data for a sprint
     * Returns completed work (story points) per day
     */
    public function getBurnupChart(int $sprintId, int $userId): array
    {
        $sprint = $this->sprintModel->find($sprintId);
        if (!$sprint) {
            throw new \RuntimeException('Sprint not found');
        }

        // Check project access
        if (!$this->projectService->userHasAccess($sprint['project_id'], $userId)) {
            throw new \RuntimeException('Access denied');
        }

        // Get all issues in sprint
        $issues = $this->db->table('issues')
            ->where('sprint_id', $sprintId)
            ->get()
            ->getResultArray();

        // Get Done column ID
        $doneColumns = $this->getDoneColumns($sprint['project_id']);

        // Calculate total story points
        $totalPoints = 0;
        foreach ($issues as $issue) {
            $totalPoints += (float)($issue['estimation'] ?? 0);
        }

        // Generate date range
        $startDate = new \DateTime($sprint['start_date']);
        $endDate = new \DateTime($sprint['end_date']);
        $currentDate = clone $startDate;

        $chartData = [];

        // Get completion dates from activity logs
        $completionDates = $this->getCompletionDates($issues, $doneColumns);

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            
            // Calculate completed points up to this date (cumulative)
            $completedPoints = 0;
            foreach ($issues as $issue) {
                $issueId = $issue['id'];
                $completedDate = $completionDates[$issueId] ?? null;
                
                // If issue was completed on or before this date
                if ($completedDate && $completedDate <= $dateStr) {
                    $completedPoints += (float)($issue['estimation'] ?? 0);
                }
            }

            if ($completedPoints > $totalPoints) $completedPoints = $totalPoints;

            $chartData[] = [
                'date' => $dateStr,
                'completed' => round($completedPoints, 1),
                'scope' => $totalPoints // Scope can change if issues are added during sprint
            ];

            $currentDate->modify('+1 day');
        }

        return [
            'sprint' => $sprint,
            'total_points' => $totalPoints,
            'chart_data' => $chartData
        ];
    }

    /**
     * Get velocity chart data (story points completed per sprint)
     */
    public function getVelocityChart(int $projectId, int $userId, int $limit = 10): array
    {
        // Check project access
        if (!$this->projectService->userHasAccess($projectId, $userId)) {
            throw new \RuntimeException('Access denied');
        }

        // Get completed sprints
        $sprints = $this->sprintModel
            ->where('project_id', $projectId)
            ->where('status', 'completed')
            ->orderBy('end_date', 'DESC')
            ->limit($limit)
            ->findAll();

        $chartData = [];
        $doneColumns = $this->getDoneColumns($projectId);

        foreach ($sprints as $sprint) {
            // Get completed issues in this sprint
            $completedIssues = $this->db->table('issues')
                ->where('sprint_id', $sprint['id'])
                ->whereIn('column_id', $doneColumns)
                ->get()
                ->getResultArray();

            $completedPoints = 0;
            foreach ($completedIssues as $issue) {
                $completedPoints += (float)($issue['estimation'] ?? 0);
            }

            $chartData[] = [
                'sprint_id' => $sprint['id'],
                'sprint_name' => $sprint['name'],
                'start_date' => $sprint['start_date'],
                'end_date' => $sprint['end_date'],
                'velocity' => round($completedPoints, 1),
                'completed_issues' => count($completedIssues)
            ];
        }

        // Reverse to show oldest first
        $chartData = array_reverse($chartData);

        return [
            'project_id' => $projectId,
            'chart_data' => $chartData,
            'average_velocity' => !empty($chartData) 
                ? round(array_sum(array_column($chartData, 'velocity')) / count($chartData), 1)
                : 0
        ];
    }

    /**
     * Get lead time and cycle time
     * Lead time: from created to completed
     * Cycle time: from first move to completed
     */
    public function getLeadTimeAndCycleTime(int $projectId, int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Check project access
        if (!$this->projectService->userHasAccess($projectId, $userId)) {
            throw new \RuntimeException('Access denied');
        }

        $doneColumns = $this->getDoneColumns($projectId);

        // Get completed issues
        $builder = $this->db->table('issues')
            ->where('project_id', $projectId)
            ->whereIn('column_id', $doneColumns);

        if ($startDate) {
            $builder->where('updated_at >=', $startDate);
        }
        if ($endDate) {
            $builder->where('updated_at <=', $endDate);
        }

        $issues = $builder->get()->getResultArray();

        $leadTimes = [];
        $cycleTimes = [];
        $issuesData = [];

        foreach ($issues as $issue) {
            $createdAt = new \DateTime($issue['created_at']);
            $completedAt = new \DateTime($issue['updated_at']); // Approximate from updated_at

            // Try to get actual completion date from activity logs
            $completionLog = $this->db->table('activity_logs')
                ->where('entity_type', 'issue')
                ->where('entity_id', $issue['id'])
                ->where('action', 'status_change')
                ->like('description', 'Done', 'both')
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($completionLog) {
                $completedAt = new \DateTime($completionLog['created_at']);
            }

            // Lead time: created to completed
            $leadTime = $createdAt->diff($completedAt)->days;
            $leadTimes[] = $leadTime;

            // Cycle time: first move to completed (approximate as created to completed if no activity log)
            $firstMoveLog = $this->db->table('activity_logs')
                ->where('entity_type', 'issue')
                ->where('entity_id', $issue['id'])
                ->where('action', 'status_change')
                ->orderBy('created_at', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($firstMoveLog) {
                $firstMoveAt = new \DateTime($firstMoveLog['created_at']);
                $cycleTime = $firstMoveAt->diff($completedAt)->days;
            } else {
                $cycleTime = $leadTime; // Use lead time as fallback
            }

            $cycleTimes[] = $cycleTime;

            $issuesData[] = [
                'issue_id' => $issue['id'],
                'issue_key' => $issue['issue_key'] ?? '',
                'title' => $issue['title'],
                'lead_time' => $leadTime,
                'cycle_time' => $cycleTime,
                'created_at' => $issue['created_at'],
                'completed_at' => $completedAt->format('Y-m-d H:i:s')
            ];
        }

        return [
            'project_id' => $projectId,
            'total_issues' => count($issues),
            'average_lead_time' => !empty($leadTimes) ? round(array_sum($leadTimes) / count($leadTimes), 1) : 0,
            'average_cycle_time' => !empty($cycleTimes) ? round(array_sum($cycleTimes) / count($cycleTimes), 1) : 0,
            'median_lead_time' => $this->calculateMedian($leadTimes),
            'median_cycle_time' => $this->calculateMedian($cycleTimes),
            'issues' => $issuesData
        ];
    }

    /**
     * Get productivity per user
     */
    public function getProductivityPerUser(int $projectId, int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Check project access
        if (!$this->projectService->userHasAccess($projectId, $userId)) {
            throw new \RuntimeException('Access denied');
        }

        $doneColumns = $this->getDoneColumns($projectId);

        // Get completed issues
        $builder = $this->db->table('issues')
            ->select('issues.*, users.full_name as assignee_name, users.email as assignee_email')
            ->join('users', 'users.id = issues.assignee_id', 'left')
            ->where('issues.project_id', $projectId)
            ->whereIn('issues.column_id', $doneColumns)
            ->where('issues.assignee_id IS NOT NULL', null, false);

        if ($startDate) {
            $builder->where('issues.updated_at >=', $startDate);
        }
        if ($endDate) {
            $builder->where('issues.updated_at <=', $endDate);
        }

        $issues = $builder->get()->getResultArray();

        $userProductivity = [];

        foreach ($issues as $issue) {
            $assigneeId = $issue['assignee_id'];
            $assigneeName = $issue['assignee_name'] ?? $issue['assignee_email'] ?? 'Unknown';

            if (!isset($userProductivity[$assigneeId])) {
                $userProductivity[$assigneeId] = [
                    'user_id' => $assigneeId,
                    'user_name' => $assigneeName,
                    'user_email' => $issue['assignee_email'] ?? '',
                    'completed_issues' => 0,
                    'completed_story_points' => 0,
                    'issues' => []
                ];
            }

            $userProductivity[$assigneeId]['completed_issues']++;
            $userProductivity[$assigneeId]['completed_story_points'] += (float)($issue['estimation'] ?? 0);
            $userProductivity[$assigneeId]['issues'][] = [
                'issue_id' => $issue['id'],
                'issue_key' => $issue['issue_key'] ?? '',
                'title' => $issue['title'],
                'estimation' => (float)($issue['estimation'] ?? 0),
                'completed_at' => $issue['updated_at']
            ];
        }

        // Convert to array and sort by story points
        $result = array_values($userProductivity);
        usort($result, fn($a, $b) => $b['completed_story_points'] <=> $a['completed_story_points']);

        return [
            'project_id' => $projectId,
            'users' => $result,
            'total_users' => count($result),
            'total_issues' => array_sum(array_column($result, 'completed_issues')),
            'total_story_points' => array_sum(array_column($result, 'completed_story_points'))
        ];
    }

    /**
     * Get Done columns for a project
     */
    protected function getDoneColumns(int $projectId): array
    {
        $boards = $this->db->table('boards')
            ->where('project_id', $projectId)
            ->get()
            ->getResultArray();

        if (empty($boards)) {
            return [];
        }

        $boardIds = array_column($boards, 'id');

        $columns = $this->db->table('columns')
            ->whereIn('board_id', $boardIds)
            ->get()
            ->getResultArray();

        $doneColumnIds = [];
        foreach ($columns as $column) {
            $columnName = strtolower($column['name'] ?? '');
            if (strpos($columnName, 'done') !== false || 
                strpos($columnName, 'completed') !== false ||
                strpos($columnName, 'finished') !== false) {
                $doneColumnIds[] = $column['id'];
            }
        }

        return $doneColumnIds;
    }

    /**
     * Get completion dates for issues from activity logs
     */
    protected function getCompletionDates(array $issues, array $doneColumnIds): array
    {
        if (empty($doneColumnIds)) {
            return [];
        }

        $issueIds = array_column($issues, 'id');
        if (empty($issueIds)) {
            return [];
        }

        // Get status change logs to Done columns
        $logs = $this->db->table('activity_logs')
            ->where('entity_type', 'issue')
            ->whereIn('entity_id', $issueIds)
            ->where('action', 'status_change')
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        $completionDates = [];
        foreach ($logs as $log) {
            $issueId = $log['entity_id'];
            
            // Parse metadata from description to check if it's to Done column
            if (preg_match('/Metadata: (.+)/', $log['description'], $matches)) {
                $metadata = json_decode($matches[1], true);
                if ($metadata && isset($metadata['to_column_id']) && in_array($metadata['to_column_id'], $doneColumnIds)) {
                    $completionDates[$issueId] = date('Y-m-d', strtotime($log['created_at']));
                }
            } else {
                // Fallback: check description for "Done" keywords
                $description = strtolower($log['description'] ?? '');
                if (strpos($description, 'done') !== false || 
                    strpos($description, 'completed') !== false ||
                    strpos($description, 'finished') !== false) {
                    $completionDates[$issueId] = date('Y-m-d', strtotime($log['created_at']));
                }
            }
        }

        // Also check current status for issues already in Done
        foreach ($issues as $issue) {
            if (in_array($issue['column_id'], $doneColumnIds) && !isset($completionDates[$issue['id']])) {
                // Use updated_at as approximate completion date
                $completionDates[$issue['id']] = date('Y-m-d', strtotime($issue['updated_at']));
            }
        }

        return $completionDates;
    }

    /**
     * Calculate ideal burndown line
     */
    protected function calculateIdealBurndown(float $totalPoints, \DateTime $startDate, \DateTime $endDate, \DateTime $currentDate): float
    {
        $totalDays = $startDate->diff($endDate)->days + 1;
        $daysPassed = $startDate->diff($currentDate)->days;
        
        if ($totalDays == 0) {
            return $totalPoints;
        }

        $pointsPerDay = $totalPoints / $totalDays;
        $idealRemaining = $totalPoints - ($pointsPerDay * $daysPassed);

        return max(0, round($idealRemaining, 1));
    }

    /**
     * Calculate median of array
     */
    protected function calculateMedian(array $values): float
    {
        if (empty($values)) {
            return 0;
        }

        sort($values);
        $count = count($values);
        $middle = floor(($count - 1) / 2);

        if ($count % 2) {
            return $values[$middle];
        } else {
            return ($values[$middle] + $values[$middle + 1]) / 2;
        }
    }
}
