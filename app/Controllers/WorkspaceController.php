<?php

namespace App\Controllers;

class WorkspaceController extends BaseController
{
    public function index()
    {
        $auth = session('auth_user');
        if (!$auth) return redirect()->to('/login');

        $roles = (array)($auth['roles'] ?? []);

        if (in_array('admin', $roles, true)) return redirect()->to('/admin');
        if (in_array('editor', $roles, true)) return redirect()->to('/editor');
        if (in_array('reviewer', $roles, true)) return redirect()->to('/reviewer');
        return redirect()->to('/author');
    }
}
