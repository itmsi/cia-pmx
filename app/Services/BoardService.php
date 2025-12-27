<?php

namespace App\Services;

use App\Models\BoardModel;
use App\Services\ActivityLogService;

class BoardService
{
    protected BoardModel $boardModel;
    protected ActivityLogService $logService;

    public function __construct()
    {
        $this->boardModel = new BoardModel();
        $this->logService = new ActivityLogService();
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
        $this->logService->log(
            'delete',
            'board',
            $boardId,
            'Board deleted'
        );


        return $this->boardModel
            ->where('id', $boardId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function updateBoard(int $boardId, int $userId, string $name)
    {
        $this->logService->log(
            'update',
            'board',
            $boardId,
            'Board updated'
        );
        
        return $this->boardModel
            ->where('id', $boardId)
            ->where('user_id', $userId)
            ->set(['name' => $name])
            ->update();
    }

}
