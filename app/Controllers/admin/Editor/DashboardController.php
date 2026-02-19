<?php

namespace App\Controllers\Editor;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        return view('dash/editor', ['title' => 'Editor Dashboard']);
    }
}
