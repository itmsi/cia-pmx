<?php

namespace App\Services;

use App\Models\ColumnModel;
use App\Models\IssueModel;

class WorkflowService
{
    protected $db;
    protected ColumnModel $columnModel;
    protected IssueModel $issueModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->columnModel = new ColumnModel();
        $this->issueModel = new IssueModel();
    }

    /**
     * Check if transition from one column to another is allowed
     */
    public function canTransition(int $fromColumnId, int $toColumnId, ?int $boardId = null, ?int $issueId = null): array
    {
        // Same column is always allowed (no change)
        if ($fromColumnId == $toColumnId) {
            return ['allowed' => true, 'message' => null];
        }

        // Get rules for this transition
        $rules = $this->getRulesForTransition($fromColumnId, $toColumnId, $boardId);

        // If no rules found, allow by default (backward compatibility)
        if (empty($rules)) {
            return ['allowed' => true, 'message' => null];
        }

        // Check each rule
        foreach ($rules as $rule) {
            if ($rule['rule_type'] === 'blocked') {
                return [
                    'allowed' => false,
                    'message' => $rule['message'] ?? "Transition from this status is not allowed"
                ];
            }

            if ($rule['rule_type'] === 'conditional' && $issueId) {
                // Check condition
                if (!$this->checkCondition($rule, $issueId)) {
                    return [
                        'allowed' => false,
                        'message' => $rule['message'] ?? "Transition condition not met"
                    ];
                }
            }
        }

        return ['allowed' => true, 'message' => null];
    }

    /**
     * Get workflow rules for a transition
     */
    protected function getRulesForTransition(int $fromColumnId, int $toColumnId, ?int $boardId = null): array
    {
        $builder = $this->db->table('workflow_rules')
            ->where('from_column_id', $fromColumnId)
            ->where('to_column_id', $toColumnId)
            ->where('is_active', true);

        // Check board-specific rules first, then global rules
        if ($boardId) {
            $builder->groupStart()
                ->where('board_id', $boardId)
                ->orWhere('board_id', null)
                ->groupEnd()
                ->orderBy('board_id', 'DESC'); // Board-specific first
        } else {
            $builder->where('board_id', null);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Check conditional rule
     */
    protected function checkCondition(array $rule, int $issueId): bool
    {
        if (empty($rule['condition'])) {
            return true;
        }

        $condition = json_decode($rule['condition'], true);
        if (!is_array($condition)) {
            return true;
        }

        $issue = $this->issueModel->find($issueId);
        if (!$issue) {
            return false;
        }

        // Check conditions
        foreach ($condition as $key => $value) {
            switch ($key) {
                case 'require_assignee':
                    if ($value && empty($issue['assignee_id'])) {
                        return false;
                    }
                    break;
                case 'require_description':
                    if ($value && empty($issue['description'])) {
                        return false;
                    }
                    break;
                case 'min_priority':
                    $priorityOrder = ['lowest' => 1, 'low' => 2, 'medium' => 3, 'high' => 4, 'critical' => 5];
                    $currentPriority = $priorityOrder[$issue['priority']] ?? 0;
                    $minPriority = $priorityOrder[$value] ?? 0;
                    if ($currentPriority < $minPriority) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Add workflow rule
     */
    public function addRule(int $fromColumnId, int $toColumnId, string $ruleType, ?int $boardId = null, ?string $message = null, ?array $condition = null): int
    {
        return $this->db->table('workflow_rules')->insert([
            'board_id' => $boardId,
            'from_column_id' => $fromColumnId,
            'to_column_id' => $toColumnId,
            'rule_type' => $ruleType,
            'condition' => $condition ? json_encode($condition) : null,
            'message' => $message,
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get all workflow rules for a board
     */
    public function getRulesForBoard(?int $boardId = null): array
    {
        $builder = $this->db->table('workflow_rules')
            ->select('workflow_rules.*, from_col.name as from_column_name, to_col.name as to_column_name')
            ->join('columns as from_col', 'from_col.id = workflow_rules.from_column_id')
            ->join('columns as to_col', 'to_col.id = workflow_rules.to_column_id');

        if ($boardId) {
            $builder->where('workflow_rules.board_id', $boardId)
                ->orWhere('workflow_rules.board_id', null);
        } else {
            $builder->where('workflow_rules.board_id', null);
        }

        return $builder->orderBy('workflow_rules.board_id', 'DESC')
            ->orderBy('from_col.position', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Delete workflow rule
     */
    public function deleteRule(int $ruleId): bool
    {
        return $this->db->table('workflow_rules')
            ->where('id', $ruleId)
            ->delete();
    }

    /**
     * Toggle rule active status
     */
    public function toggleRule(int $ruleId, bool $isActive): bool
    {
        return $this->db->table('workflow_rules')
            ->where('id', $ruleId)
            ->update([
                'is_active' => $isActive,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Get allowed transitions from a column
     */
    public function getAllowedTransitions(int $fromColumnId, ?int $boardId = null): array
    {
        // Get all columns
        $allColumns = $this->columnModel->findAll();
        $allowed = [];

        foreach ($allColumns as $column) {
            $check = $this->canTransition($fromColumnId, $column['id'], $boardId);
            if ($check['allowed']) {
                $allowed[] = $column;
            }
        }

        return $allowed;
    }
}

