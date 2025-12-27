<?php

namespace App\Services;

use App\Models\ColumnModel;

class ColumnService
{
    protected ColumnModel $columnModel;

    public function __construct()
    {
        $this->columnModel = new ColumnModel();
    }

    public function createColumn(int $boardId, string $name, int $position)
    {
        return $this->columnModel->insert([
            'board_id' => $boardId,
            'name'     => $name,
            'position' => $position,
        ]);
    }

    public function getColumnsByBoard(int $boardId)
    {
        return $this->columnModel
            ->where('board_id', $boardId)
            ->orderBy('position', 'ASC')
            ->findAll();
    }

    public function updateColumn(int $columnId, int $boardId, string $name)
    {
        return $this->columnModel
            ->where('id', $columnId)
            ->where('board_id', $boardId)
            ->set(['name' => $name])
            ->update();
    }

    public function deleteColumn(int $columnId, int $boardId)
    {
        return $this->columnModel
            ->where('id', $columnId)
            ->where('board_id', $boardId)
            ->delete();
    }

    public function getMaxPosition(int $boardId): int
    {
        $row = $this->columnModel
            ->selectMax('position')
            ->where('board_id', $boardId)
            ->first();

        return $row['position'] ?? 0;
    }

    public function getColumnById(int $id)
    {
        return $this->columnModel->find($id);
    }

}