<?php

namespace App\Services;

use App\Models\CardModel;
use CodeIgniter\Database\BaseConnection;
use App\Services\ActivityLogService;

class CardService
{
    protected CardModel $cardModel;
    protected ActivityLogService $logService;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->cardModel = new CardModel();
        $this->db = \Config\Database::connect();
        $this->logService = new ActivityLogService();
    }

    public function createCard(int $columnId, string $title, int $position)
    {
        return $this->cardModel->insert([
            'column_id' => $columnId,
            'title'     => $title,
            'position'  => $position,
        ]);
    }

    public function getCardsByColumn(int $columnId)
    {
        return $this->cardModel
            ->where('column_id', $columnId)
            ->orderBy('position', 'ASC')
            ->findAll();
    }

    public function moveCard(int $cardId, int $targetColumnId, int $position)
    {
        return $this->cardModel->update($cardId, [
            'column_id' => $targetColumnId,
            'position'  => $position,
        ]);
    }

    public function updateCard(int $cardId, int $columnId, string $title)
    {
        $this->logService->log(
            'update',
            'card',
            $cardId,
            'Card updated'
        );

        return $this->cardModel
            ->where('id', $cardId)
            ->where('column_id', $columnId)
            ->set(['title' => $title])
            ->update();
    }

    public function deleteCard(int $cardId, int $columnId)
    {
        $this->logService->log(
            'delete',
            'card',
            $cardId,
            'Card deleted'
        );

        return $this->cardModel
            ->where('id', $cardId)
            ->where('column_id', $columnId)
            ->delete();
    }

    public function getCardById(int $id)
    {
        return $this->cardModel->find($id);
    }

    public function getMaxPosition(int $columnId): int
    {
        $row = $this->cardModel
            ->selectMax('position')
            ->where('column_id', $columnId)
            ->first();

        return $row['position'] ?? 0;
    }

    private function reindexColumn(int $columnId): void
    {
        $cards = $this->cardModel
        ->where('column_id', $columnId)
        ->orderBy('position', 'ASC')
        ->findAll();

        if (count($cards) > 500) {
            throw new \RuntimeException('Too many cards to reindex');
        }

        foreach ($cards as $index => $card) {
            $this->cardModel->update($card['id'], [
                'position' => $index
            ]);
        }
    }

    public function moveWithinColumn(int $cardId, int $columnId, int $newPosition)
    {
        $this->db->transStart();

        // Temporarily move card out of the way
        $this->cardModel->update($cardId, ['position' => -1]);

        // Shift cards down
        $this->cardModel
            ->where('column_id', $columnId)
            ->where('position >=', $newPosition)
            ->set('position', 'position + 1', false)
            ->update();

        // Place card
        $this->cardModel->update($cardId, [
            'column_id' => $columnId,
            'position'  => $newPosition
        ]);

        $this->reindexColumn($columnId);

        $this->db->transComplete();

        $this->logService->log(
            'move',
            'card',
            $cardId,
            "Card moved from column {$fromColumnId} to {$toColumnId}"
        );
    }

    public function moveToAnotherColumn(
        int $cardId,
        int $fromColumnId,
        int $toColumnId,
        int $newPosition
    ) {
        $this->db->transStart();

        // Remove from source column
        $this->cardModel->update($cardId, [
            'column_id' => $toColumnId,
            'position'  => $newPosition
        ]);

        // Shift target column
        $this->cardModel
            ->where('column_id', $toColumnId)
            ->where('position >=', $newPosition)
            ->set('position', 'position + 1', false)
            ->update();

        // Reindex both columns
        $this->reindexColumn($fromColumnId);
        $this->reindexColumn($toColumnId);

        $this->db->transComplete();

        $this->logService->log(
            'move',
            'card',
            $cardId,
            "Card moved from column {$fromColumnId} to {$toColumnId}"
        );
    }

    public function getCardsByBoard(int $boardId): array
    {
        return $this->cardModel
            ->select('cards.*')
            ->join('columns', 'columns.id = cards.column_id')
            ->where('columns.board_id', $boardId)
            ->orderBy('cards.position', 'ASC')
            ->findAll();
    }


}
