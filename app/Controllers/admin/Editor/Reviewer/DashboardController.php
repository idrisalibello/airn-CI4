<?php

namespace App\Controllers\Reviewer;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        return view('dash/reviewer', ['title' => 'Reviewer Dashboard']);
    }
}
