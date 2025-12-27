<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\BoardService;
use App\Services\ColumnService;
use App\Services\CardService;

class BoardController extends BaseController
{
    protected BoardService $boardService;
    protected ColumnService $columnService;
    protected CardService $cardService;


    public function __construct()
    {
        $this->boardService = new BoardService();
        $this->columnService = new ColumnService();
        $this->cardService   = new CardService();
    }

    // GET /boards
    public function index()
    {
        $boards = $this->boardService->getBoardsForUser(
            session()->get('user_id')
        );

        return view('boards/index', [
            'boards' => $boards
        ]);
    }


    // public function show($boardId)
    // {
    //     // 1️⃣ Get board
    //     $currentBoard = $this->boardService->getUserBoardById(
    //         (int) $boardId,
    //         session()->get('user_id')
    //     );

    //     if (! $currentBoard) {
    //         throw new \CodeIgniter\Exceptions\PageNotFoundException('Board not found');
    //     }

    //     // 2️⃣ Get columns
    //     $columns = $this->columnService->getColumnsByBoard((int) $boardId);

    //     // 3️⃣ Attach cards to each column
    //     $cards = $this->cardService->getCardsByBoard($boardId);

    //     $cardsByColumn = [];
    //     foreach ($cards as $card) {
    //         $cardsByColumn[$card['column_id']][] = $card;
    //     }

    //     foreach ($columns as &$column) {
    //         $column['cards'] = $cardsByColumn[$column['id']] ?? [];
    //     }

    //     $columnOrder = [];
    //     foreach ($columns as $col) {
    //         $columnOrder[$col['position']] = $col['id'];
    //     }
    //     return view('boards/show', [
    //         'board'       => $currentBoard,
    //         'columns'     => $columns,
    //         'columnOrder' => $columnOrder
    //     ]);
    // }

    public function show($boardId)
    {
        $userId = session()->get('user_id');

        // 1️⃣ Fetch board (ownership enforced)
        $board = $this->boardService->getUserBoardById(
            (int) $boardId,
            (int) $userId
        );

        if (! $board) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Board not found');
        }

        // 2️⃣ Fetch columns for this board (ordered)
        $columns = $this->columnService->getColumnsByBoard((int) $boardId);

        // 3️⃣ Fetch ALL cards for this board in ONE query (fix N+1)
        $cards = $this->cardService->getCardsByBoard((int) $boardId);

        // 4️⃣ Group cards by column_id
        $cardsByColumn = [];
        foreach ($cards as $card) {
            $cardsByColumn[$card['column_id']][] = $card;
        }

        // 5️⃣ Attach cards to each column
        foreach ($columns as &$column) {
            $column['cards'] = $cardsByColumn[$column['id']] ?? [];
        }
        unset($column); // safety

        // 6️⃣ Build column position → column_id map
        $columnOrder = [];
        foreach ($columns as $column) {
            $columnOrder[$column['position']] = $column['id'];
        }

        // 7️⃣ Render view
        return view('boards/show', [
            'board'       => $board,
            'columns'     => $columns,
            'columnOrder' => $columnOrder
        ]);
    }


    // POST /boards
    public function create()
    {
        $rules = [
            'name' => 'required|min_length[3]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $this->boardService->createBoard(
            $this->request->getPost('name'),
            session()->get('user_id')
        );

        return redirect()->to('/boards');
    }

    public function delete($boardId)
    {
        $this->boardService->deleteBoard(
            (int) $boardId,
            session()->get('user_id')
        );

        return redirect()->to('/boards');
    }

    public function edit($boardId)
    {
        $board = $this->boardService->getUserBoardById(
            (int) $boardId,
            session()->get('user_id')
        );

        if (! $board) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Board not found');
        }

        return view('boards/edit', [
            'board' => $board
        ]);
    }

    public function update($boardId)
    {
        $rules = [
            'name' => 'required|min_length[3]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $this->boardService->updateBoard(
            (int) $boardId,
            session()->get('user_id'),
            $this->request->getPost('name')
        );

        return redirect()->to('/boards');
    }

}
