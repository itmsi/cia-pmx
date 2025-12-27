<?php

namespace App\Services;

use App\Models\BoardModel;
use App\Services\ActivityLogService;

class BoardService
{
    protected BoardModel $boardModel;
    protected ActivityLogService $logService;
    protected $db;

    public function __construct()
    {
        $this->boardModel = new BoardModel();
        $this->logService = new ActivityLogService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new board
     */
    public function createBoard(string $name, int $userId)
    {
        $boardId = $this->boardModel->insert([
            'name'    => $name,
            'user_id'=> $userId
        ]);

        $this->logService->log(
            'create',
            'board',
            $boardId,
            'Board created'
        );

        return $boardId;
    }

    /**
     * Get all boards
     */
    public function getBoardsForUser(int $userId)
    {
        return $this->boardModel
            ->where('user_id', $userId)
            ->findAll();
    }

    public function getBoardById(int $id)
    {
        return $this->boardModel->find($id);
    }

    public function getUserBoardById(int $boardId, int $userId)
    {
        return $this->boardModel
            ->where('id', $boardId)
            ->where('user_id', $userId)
            ->first();
    }

    public function deleteBoard(int $boardId, int $userId)
    {
        // Check if user can edit board (required for delete)
        if (!$this->userCanEditBoard($boardId, $userId)) {
            throw new \RuntimeException('User does not have permission to delete this board');
        }

        $this->logService->log(
            'delete',
            'board',
            $boardId,
            'Board deleted'
        );

        return $this->boardModel->delete($boardId);
    }

    public function updateBoard(int $boardId, int $userId, string $name)
    {
        // Check if user can edit board
        if (!$this->userCanEditBoard($boardId, $userId)) {
            throw new \RuntimeException('User does not have permission to edit this board');
        }

        $this->logService->log(
            'update',
            'board',
            $boardId,
            'Board updated'
        );
        
        return $this->boardModel
            ->where('id', $boardId)
            ->set(['name' => $name])
            ->update();
    }

    /**
     * Check if user can view board
     */
    public function userCanViewBoard(int $boardId, int $userId): bool
    {
        $board = $this->getBoardById($boardId);
        if (!$board) {
            return false;
        }

        // Board owner always has view access
        if ($board['user_id'] == $userId) {
            return true;
        }

        // Check if user has explicit view permission
        $permission = $this->db->table('board_permissions')
            ->where('board_id', $boardId)
            ->where('user_id', $userId)
            ->where('can_view', true)
            ->get()
            ->getRowArray();

        return $permission !== null;
    }

    /**
     * Check if user can edit board
     */
    public function userCanEditBoard(int $boardId, int $userId): bool
    {
        $board = $this->getBoardById($boardId);
        if (!$board) {
            return false;
        }

        // Board owner always has edit access
        if ($board['user_id'] == $userId) {
            return true;
        }

        // Check if user has explicit edit permission
        $permission = $this->db->table('board_permissions')
            ->where('board_id', $boardId)
            ->where('user_id', $userId)
            ->where('can_edit', true)
            ->get()
            ->getRowArray();

        return $permission !== null;
    }

    /**
     * Add user permission to board
     */
    public function addUserPermission(int $boardId, int $userId, bool $canView = false, bool $canEdit = false): bool
    {
        // Check if permission already exists
        $exists = $this->db->table('board_permissions')
            ->where('board_id', $boardId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;

        if ($exists) {
            // Update existing permission
            return $this->db->table('board_permissions')
                ->where('board_id', $boardId)
                ->where('user_id', $userId)
                ->update([
                    'can_view' => $canView,
                    'can_edit' => $canEdit,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            // Insert new permission
            return $this->db->table('board_permissions')->insert([
                'board_id' => $boardId,
                'user_id' => $userId,
                'can_view' => $canView,
                'can_edit' => $canEdit,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Remove user permission from board
     */
    public function removeUserPermission(int $boardId, int $userId): bool
    {
        $board = $this->getBoardById($boardId);
        
        // Cannot remove owner's permission
        if ($board && $board['user_id'] == $userId) {
            throw new \RuntimeException('Cannot remove board owner permission');
        }

        return $this->db->table('board_permissions')
            ->where('board_id', $boardId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Get all permissions for a board
     */
    public function getBoardPermissions(int $boardId): array
    {
        return $this->db->table('board_permissions')
            ->select('board_permissions.*, users.full_name, users.email')
            ->join('users', 'users.id = board_permissions.user_id')
            ->where('board_permissions.board_id', $boardId)
            ->get()
            ->getResultArray();
    }

    /**
     * Get boards accessible by user (including permissions)
     */
    public function getBoardsAccessibleByUser(int $userId): array
    {
        // Get boards owned by user
        $ownedBoards = $this->boardModel
            ->where('user_id', $userId)
            ->findAll();

        // Get boards with view permission
        $permissionBoards = $this->db->table('board_permissions')
            ->select('boards.*')
            ->join('boards', 'boards.id = board_permissions.board_id')
            ->where('board_permissions.user_id', $userId)
            ->where('board_permissions.can_view', true)
            ->get()
            ->getResultArray();

        // Merge and remove duplicates
        $allBoards = array_merge($ownedBoards, $permissionBoards);
        $uniqueBoards = [];
        $seenIds = [];

        foreach ($allBoards as $board) {
            if (!in_array($board['id'], $seenIds)) {
                $uniqueBoards[] = $board;
                $seenIds[] = $board['id'];
            }
        }

        return $uniqueBoards;
    }

}
