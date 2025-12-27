<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\ColumnService;

class ColumnController extends BaseController
{
    protected ColumnService $columnService;

    public function __construct()
    {
        $this->columnService = new ColumnService();
    }

    // POST /columns
    public function create()
    {
        $rules = [
            'board_id' => 'required|integer',
            'name'     => 'required|min_length[2]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back();
        }

        $boardId = (int) $this->request->getPost('board_id');

        $position = $this->columnService->getMaxPosition($boardId) + 1;

        $this->columnService->createColumn(
            $boardId,
            $this->request->getPost('name'),
            $position
        );

        return redirect()->back();
    }

    public function edit($columnId)
    {
        $column = $this->columnService
            ->getColumnById((int) $columnId);

        if (! $column) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Column not found');
        }

        return view('columns/edit', [
            'column' => $column
        ]);
    }

    public function update($columnId)
    {
        $rules = [
            'name'     => 'required|min_length[2]',
            'board_id' => 'required|integer'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $this->columnService->updateColumn(
            (int) $columnId,
            (int) $this->request->getPost('board_id'),
            $this->request->getPost('name')
        );

        return redirect()->to('/boards/' . $this->request->getPost('board_id'));
    }

    public function delete($columnId)
    {
        $boardId = (int) $this->request->getPost('board_id');

        $this->columnService->deleteColumn(
            (int) $columnId,
            $boardId
        );

        return redirect()->to('/boards/' . $boardId);
    }


    // GET /boards/{id}/columns
    public function list($boardId)
    {
        $columns = $this->columnService->getColumnsByBoard((int) $boardId);

        return $this->response->setJSON($columns);
    }
}
