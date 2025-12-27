<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ActivityLogController extends BaseController
{
    public function index()
    {
        $model = model('ActivityLogModel');
        
        $logs = model('ActivityLogModel')
            ->where('user_id', session()->get('user_id'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('activity_logs/index', [
            'logs'  => $logs,
            'pager' => $model->pager
        ]);

    }

}
