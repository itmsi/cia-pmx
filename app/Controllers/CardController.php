<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\CardService;

class CardController extends BaseController
{
    protected CardService $cardService;

    public function __construct()
    {
        $this->cardService = new CardService();
    }

    // POST /cards
    public function create()
    {
        $rules = [
            'column_id' => 'required|integer',
            'title'     => 'required|min_length[3]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back();
        }

        $columnId = (int) $this->request->getPost('column_id');
        $position = $this->cardService->getMaxPosition($columnId) + 1;

        $this->cardService->createCard(
            $columnId,
            $this->request->getPost('title'),
            $position
        );

        return redirect()->back();
    }


    // GET /columns/{id}/cards
    public function list($columnId)
    {
        $cards = $this->cardService->getCardsByColumn((int) $columnId);

        return $this->response->setJSON($cards);
    }

    // POST /cards/move
    public function move()
    {
        $rules = [
            'card_id'       => 'required|integer',
            'from_column'   => 'required|integer',
            'to_column'     => 'required|integer',
            'position'      => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back();
        }

        $cardId     = (int) $this->request->getPost('card_id');
        $fromColumn = (int) $this->request->getPost('from_column');
        $toColumn   = (int) $this->request->getPost('to_column');
        $position   = (int) $this->request->getPost('position');

        if ($fromColumn === $toColumn) {
            $this->cardService->moveWithinColumn(
                $cardId,
                $fromColumn,
                $position
            );
        } else {
            $this->cardService->moveToAnotherColumn(
                $cardId,
                $fromColumn,
                $toColumn,
                $position
            );
        }

        return redirect()->back();
    }


    public function edit($cardId)
    {
        $card = $this->cardService->getCardById((int) $cardId);

        if (! $card) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Card not found');
        }

        return view('cards/edit', [
            'card' => $card
        ]);
    }

    public function update($cardId)
    {
        $rules = [
            'title'     => 'required|min_length[3]',
            'column_id' => 'required|integer'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $this->cardService->updateCard(
            (int) $cardId,
            (int) $this->request->getPost('column_id'),
            $this->request->getPost('title')
        );

        return redirect()->to('/boards/' . $this->request->getPost('board_id'));
    }

    public function delete($cardId)
    {
        $columnId = (int) $this->request->getPost('column_id');
        $boardId  = (int) $this->request->getPost('board_id');

        $this->cardService->deleteCard($cardId, $columnId);

        return redirect()->to('/boards/' . $boardId);
    }

}
