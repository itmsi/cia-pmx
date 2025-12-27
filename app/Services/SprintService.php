<?php

namespace App\Services;

use App\Models\SprintModel;
use App\Services\ActivityLogService;
use App\Services\ProjectService;

class SprintService
{
    protected SprintModel $sprintModel;
    protected ActivityLogService $logService;
    protected ProjectService $projectService;
    protected $db;

    public function __construct()
    {
        $this->sprintModel = new SprintModel();
        $this->logService = new ActivityLogService();
        $this->projectService = new ProjectService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new sprint
     */
    public function createSprint(int $projectId, string $name, string $startDate, int $durationWeeks, array $options = []): int
    {
        // Validate project access
        $project = $this->projectService->getProjectById($projectId);
        if (!$project) {
            throw new \RuntimeException('Project not found');
        }

        // Calculate end date based on start date and duration
        $start = new \DateTime($startDate);
        $end = clone $start;
        $end->modify("+{$durationWeeks} weeks");
        $end->modify('-1 day'); // End date is last day of sprint

        // Validate duration (1-4 weeks)
        if ($durationWeeks < 1 || $durationWeeks > 4) {
            throw new \RuntimeException('Sprint duration must be between 1 and 4 weeks');
        }

        $sprintId = $this->sprintModel->insert([
            'project_id' => $projectId,
            'name' => $name,
            'goal' => $options['goal'] ?? null,
            'start_date' => $startDate,
            'end_date' => $end->format('Y-m-d'),
            'duration_weeks' => $durationWeeks,
            'status' => $options['status'] ?? 'planned',
        ]);

        $this->logService->log(
            'create',
            'sprint',
            $sprintId,
            "Sprint created: {$name}"
        );

        return $sprintId;
    }

    /**
     * Get all sprints for a project
     */
    public function getSprintsByProject(int $projectId): array
    {
        return $this->sprintModel
            ->where('project_id', $projectId)
            ->orderBy('start_date', 'DESC')
            ->findAll();
    }

    /**
     * Get sprint by ID
     */
    public function getSprintById(int $sprintId): ?array
    {
        return $this->sprintModel->find($sprintId);
    }

    /**
     * Get active sprint for a project
     */
    public function getActiveSprint(int $projectId): ?array
    {
        return $this->sprintModel
            ->where('project_id', $projectId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Update sprint
     */
    public function updateSprint(int $sprintId, array $data): bool
    {
        $sprint = $this->getSprintById($sprintId);
        if (!$sprint) {
            throw new \RuntimeException('Sprint not found');
        }

        // If duration or start_date changed, recalculate end_date
        if (isset($data['start_date']) || isset($data['duration_weeks'])) {
            $startDate = $data['start_date'] ?? $sprint['start_date'];
            $durationWeeks = $data['duration_weeks'] ?? $sprint['duration_weeks'];

            $start = new \DateTime($startDate);
            $end = clone $start;
            $end->modify("+{$durationWeeks} weeks");
            $end->modify('-1 day');

            $data['end_date'] = $end->format('Y-m-d');
        }

        $result = $this->sprintModel->update($sprintId, $data);

        if ($result) {
            $this->logService->log(
                'update',
                'sprint',
                $sprintId,
                'Sprint updated'
            );
        }

        return $result;
    }

    /**
     * Delete sprint
     */
    public function deleteSprint(int $sprintId): bool
    {
        $sprint = $this->getSprintById($sprintId);
        if (!$sprint) {
            throw new \RuntimeException('Sprint not found');
        }

        // Check if sprint has issues
        $issueCount = $this->db->table('issues')
            ->where('sprint_id', $sprintId)
            ->countAllResults();

        if ($issueCount > 0) {
            throw new \RuntimeException('Cannot delete sprint: has assigned issues. Remove issues first.');
        }

        $result = $this->sprintModel->delete($sprintId);

        if ($result) {
            $this->logService->log(
                'delete',
                'sprint',
                $sprintId,
                'Sprint deleted'
            );
        }

        return $result;
    }

    /**
     * Start sprint (change status to active)
     */
    public function startSprint(int $sprintId): bool
    {
        $sprint = $this->getSprintById($sprintId);
        if (!$sprint) {
            throw new \RuntimeException('Sprint not found');
        }

        // Check if there's already an active sprint in the project
        $activeSprint = $this->getActiveSprint($sprint['project_id']);
        if ($activeSprint && $activeSprint['id'] != $sprintId) {
            throw new \RuntimeException('There is already an active sprint in this project');
        }

        // Validate start date
        $today = new \DateTime();
        $startDate = new \DateTime($sprint['start_date']);
        if ($startDate > $today) {
            throw new \RuntimeException('Cannot start sprint: start date is in the future');
        }

        $result = $this->sprintModel->update($sprintId, [
            'status' => 'active'
        ]);

        if ($result) {
            $this->logService->log(
                'update',
                'sprint',
                $sprintId,
                'Sprint started'
            );
        }

        return $result;
    }

    /**
     * Complete sprint (change status to completed)
     * Returns array with result and carry-over info
     */
    public function completeSprint(int $sprintId, bool $autoCarryOver = true): array
    {
        $sprint = $this->getSprintById($sprintId);
        if (!$sprint) {
            throw new \RuntimeException('Sprint not found');
        }

        if ($sprint['status'] !== 'active') {
            throw new \RuntimeException('Only active sprints can be completed');
        }

        $result = $this->sprintModel->update($sprintId, [
            'status' => 'completed'
        ]);

        $carryOverInfo = ['carried_over' => 0, 'next_sprint_id' => null];

        if ($result) {
            // Auto carry-over unfinished issues
            if ($autoCarryOver) {
                $carryOverInfo = $this->carryOverUnfinishedIssues($sprintId);
            }

            $logMessage = 'Sprint completed';
            if ($autoCarryOver && $carryOverInfo['carried_over'] > 0) {
                if ($carryOverInfo['next_sprint_id']) {
                    $logMessage .= ". {$carryOverInfo['carried_over']} issue(s) carried over to {$carryOverInfo['next_sprint_name']}";
                } else {
                    $logMessage .= ". {$carryOverInfo['carried_over']} issue(s) moved to backlog";
                }
            }

            $this->logService->log(
                'update',
                'sprint',
                $sprintId,
                $logMessage
            );
        }

        return [
            'success' => $result,
            'carry_over' => $carryOverInfo
        ];
    }

    /**
     * Carry over unfinished issues to next sprint
     */
    public function carryOverUnfinishedIssues(int $completedSprintId): array
    {
        $sprint = $this->getSprintById($completedSprintId);
        if (!$sprint) {
            throw new \RuntimeException('Sprint not found');
        }

        // Get all issues in the completed sprint
        $issues = $this->getIssuesInSprint($completedSprintId);
        
        if (empty($issues)) {
            return ['carried_over' => 0, 'next_sprint_id' => null];
        }

        // Get "Done" or "Completed" columns to identify finished issues
        // First get boards for this project
        $boards = $this->db->table('boards')
            ->where('project_id', $sprint['project_id'])
            ->get()
            ->getResultArray();

        $boardIds = array_column($boards, 'id');
        
        $doneColumnIds = [];
        if (!empty($boardIds)) {
            $doneColumns = $this->db->table('columns')
                ->whereIn('board_id', $boardIds)
                ->groupStart()
                    ->like('name', 'done', 'both')
                    ->orLike('name', 'completed', 'both')
                    ->orLike('name', 'finished', 'both')
                ->groupEnd()
                ->get()
                ->getResultArray();

            $doneColumnIds = array_column($doneColumns, 'id');
        }

        // Filter unfinished issues (not in Done/Completed columns)
        $unfinishedIssues = [];
        foreach ($issues as $issue) {
            if (!in_array($issue['column_id'], $doneColumnIds)) {
                $unfinishedIssues[] = $issue;
            }
        }

        if (empty($unfinishedIssues)) {
            return ['carried_over' => 0, 'next_sprint_id' => null];
        }

        // Get or create next sprint
        $nextSprint = $this->getOrCreateNextSprint($sprint['project_id'], $sprint['end_date']);

        if (!$nextSprint) {
            // If no next sprint and can't create, just remove sprint_id from issues
            foreach ($unfinishedIssues as $issue) {
                $this->db->table('issues')
                    ->where('id', $issue['id'])
                    ->update(['sprint_id' => null]);
            }

            $this->logService->log(
                'update',
                'sprint',
                $completedSprintId,
                count($unfinishedIssues) . ' unfinished issues moved to backlog'
            );

            return ['carried_over' => count($unfinishedIssues), 'next_sprint_id' => null];
        }

        // Move unfinished issues to next sprint
        $carriedOver = 0;
        foreach ($unfinishedIssues as $issue) {
            $this->db->table('issues')
                ->where('id', $issue['id'])
                ->update(['sprint_id' => $nextSprint['id']]);
            $carriedOver++;
        }

        $this->logService->log(
            'update',
            'sprint',
            $completedSprintId,
            "{$carriedOver} unfinished issues carried over to sprint: {$nextSprint['name']}"
        );

        return [
            'carried_over' => $carriedOver,
            'next_sprint_id' => $nextSprint['id'],
            'next_sprint_name' => $nextSprint['name']
        ];
    }

    /**
     * Get next sprint or create one if doesn't exist
     */
    protected function getOrCreateNextSprint(int $projectId, string $currentSprintEndDate): ?array
    {
        // Calculate next sprint start date (day after current sprint ends)
        $endDate = new \DateTime($currentSprintEndDate);
        $nextStartDate = clone $endDate;
        $nextStartDate->modify('+1 day');

        // Try to find next planned sprint
        $nextSprint = $this->sprintModel
            ->where('project_id', $projectId)
            ->where('status', 'planned')
            ->where('start_date >=', $nextStartDate->format('Y-m-d'))
            ->orderBy('start_date', 'ASC')
            ->first();

        if ($nextSprint) {
            return $nextSprint;
        }

        // If no next sprint found, create a new one
        // Use same duration as typical sprint (2 weeks default)
        $durationWeeks = 2;

        // Calculate end date
        $endDate = clone $nextStartDate;
        $endDate->modify("+{$durationWeeks} weeks");
        $endDate->modify('-1 day');

        // Generate sprint name (Sprint {number})
        $sprintCount = $this->sprintModel
            ->where('project_id', $projectId)
            ->countAllResults();
        
        $sprintName = 'Sprint ' . ($sprintCount + 1);

        $newSprintId = $this->sprintModel->insert([
            'project_id' => $projectId,
            'name' => $sprintName,
            'start_date' => $nextStartDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'duration_weeks' => $durationWeeks,
            'status' => 'planned'
        ]);

        if ($newSprintId) {
            $this->logService->log(
                'create',
                'sprint',
                $newSprintId,
                "Auto-created next sprint: {$sprintName}"
            );

            return $this->getSprintById($newSprintId);
        }

        return null;
    }

    /**
     * Check if issue is completed (in Done/Completed column)
     */
    protected function isIssueCompleted(array $issue): bool
    {
        $column = $this->db->table('columns')
            ->where('id', $issue['column_id'])
            ->get()
            ->getRowArray();

        if (!$column) {
            return false;
        }

        $columnName = strtolower($column['name'] ?? '');
        
        return strpos($columnName, 'done') !== false || 
               strpos($columnName, 'completed') !== false ||
               strpos($columnName, 'finished') !== false;
    }

    /**
     * Get issues in sprint
     */
    public function getIssuesInSprint(int $sprintId): array
    {
        return $this->db->table('issues')
            ->where('sprint_id', $sprintId)
            ->orderBy('position', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Add issue to sprint
     */
    public function addIssueToSprint(int $sprintId, int $issueId): bool
    {
        $sprint = $this->getSprintById($sprintId);
        if (!$sprint) {
            throw new \RuntimeException('Sprint not found');
        }

        // Check if sprint is active or planned
        if ($sprint['status'] === 'completed') {
            throw new \RuntimeException('Cannot add issues to completed sprint');
        }

        return $this->db->table('issues')
            ->where('id', $issueId)
            ->update(['sprint_id' => $sprintId]);
    }

    /**
     * Remove issue from sprint
     */
    public function removeIssueFromSprint(int $issueId): bool
    {
        return $this->db->table('issues')
            ->where('id', $issueId)
            ->update(['sprint_id' => null]);
    }

    /**
     * Calculate sprint capacity (total story points)
     */
    public function calculateSprintCapacity(int $sprintId): array
    {
        $sprint = $this->getSprintById($sprintId);
        if (!$sprint) {
            throw new \RuntimeException('Sprint not found');
        }

        $issues = $this->getIssuesInSprint($sprintId);

        $totalEstimation = 0;
        $completedEstimation = 0;
        $inProgressEstimation = 0;
        $todoEstimation = 0;

        foreach ($issues as $issue) {
            $estimation = (float)($issue['estimation'] ?? 0);
            $totalEstimation += $estimation;

            // Get column name to determine status
            $column = $this->db->table('columns')
                ->where('id', $issue['column_id'])
                ->get()
                ->getRowArray();

            $columnName = strtolower($column['name'] ?? '');

            if (strpos($columnName, 'done') !== false || strpos($columnName, 'completed') !== false) {
                $completedEstimation += $estimation;
            } elseif (strpos($columnName, 'progress') !== false || strpos($columnName, 'doing') !== false) {
                $inProgressEstimation += $estimation;
            } else {
                $todoEstimation += $estimation;
            }
        }

        return [
            'total' => $totalEstimation,
            'completed' => $completedEstimation,
            'in_progress' => $inProgressEstimation,
            'todo' => $todoEstimation,
            'completion_percentage' => $totalEstimation > 0 ? round(($completedEstimation / $totalEstimation) * 100, 2) : 0
        ];
    }
}

