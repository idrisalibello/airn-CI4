<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        return view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }
}
