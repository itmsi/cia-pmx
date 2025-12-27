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
        $boards = $this->boardService->getBoardsAccessibleByUser(
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

        // Check if user can view board
        if (!$this->boardService->userCanViewBoard((int)$boardId, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Board not found or access denied');
        }

        // 1️⃣ Fetch board
        $board = $this->boardService->getBoardById((int)$boardId);

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

    public function edit($boardId)
    {
        $userId = session()->get('user_id');

        // Check if user can edit board
        if (!$this->boardService->userCanEditBoard((int)$boardId, $userId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Board not found or access denied');
        }

        $board = $this->boardService->getBoardById((int)$boardId);

        if (! $board) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Board not found');
        }

        return view('boards/edit', [
            'board' => $board
        ]);
    }

    public function update($boardId)
    {
        $userId = session()->get('user_id');

        // Check if user can edit board
        if (!$this->boardService->userCanEditBoard((int)$boardId, $userId)) {
            return redirect()->back()
                ->with('error', 'You do not have permission to edit this board');
        }

        $rules = [
            'name' => 'required|min_length[3]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $this->boardService->updateBoard(
            (int) $boardId,
            $userId,
            $this->request->getPost('name')
        );

        return redirect()->to('/boards');
    }

    public function delete($boardId)
    {
        $userId = session()->get('user_id');

        // Check if user can edit board (required for delete)
        if (!$this->boardService->userCanEditBoard((int)$boardId, $userId)) {
            return redirect()->back()
                ->with('error', 'You do not have permission to delete this board');
        }

        $this->boardService->deleteBoard(
            (int) $boardId,
            $userId
        );

        return redirect()->to('/boards');
    }

    /**
     * Show board permissions
     * GET /boards/{id}/permissions
     */
    public function showPermissions($boardId)
    {
        $userId = session()->get('user_id');

        // Check if user can edit board (only board owner can manage permissions)
        $board = $this->boardService->getBoardById((int)$boardId);
        if (!$board || $board['user_id'] != $userId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Board not found or access denied');
        }

        $permissions = $this->boardService->getBoardPermissions((int)$boardId);

        return view('boards/permissions', [
            'board' => $board,
            'permissions' => $permissions
        ]);
    }

    /**
     * Add user permission to board
     * POST /boards/{id}/permissions
     */
    public function addPermission($boardId)
    {
        $userId = session()->get('user_id');

        // Check if user is board owner
        $board = $this->boardService->getBoardById((int)$boardId);
        if (!$board || $board['user_id'] != $userId) {
            return redirect()->back()
                ->with('error', 'Only board owner can manage permissions');
        }

        $rules = [
            'user_id' => 'required|integer',
            'can_view' => 'permit_empty|in_list[0,1]',
            'can_edit' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $this->boardService->addUserPermission(
                (int)$boardId,
                (int)$this->request->getPost('user_id'),
                (bool)$this->request->getPost('can_view'),
                (bool)$this->request->getPost('can_edit')
            );

            return redirect()->back()
                ->with('success', 'Permission added successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove user permission from board
     * POST /boards/{id}/permissions/{userId}/remove
     */
    public function removePermission($boardId, $userId)
    {
        $currentUserId = session()->get('user_id');

        // Check if user is board owner
        $board = $this->boardService->getBoardById((int)$boardId);
        if (!$board || $board['user_id'] != $currentUserId) {
            return redirect()->back()
                ->with('error', 'Only board owner can manage permissions');
        }

        try {
            $this->boardService->removeUserPermission((int)$boardId, (int)$userId);

            return redirect()->back()
                ->with('success', 'Permission removed successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

}
