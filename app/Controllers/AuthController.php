<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserRoleModel;

class AuthController extends BaseController
{
    public function loginForm()
    {
        return view('auth/login', ['error' => session('error')]);
    }

    public function login()
    {
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        if ($email === '' || $password === '') {
            return redirect()->to('/login')->with('error', 'Email and password required.');
        }

        $users = new UserModel();
        $user = $users->where('email', $email)->first();

        if (!$user || ($user['status'] ?? '') !== 'active') {
            return redirect()->to('/login')->with('error', 'Invalid credentials.');
        }

        if (!password_verify($password, $user['password_hash'])) {
            return redirect()->to('/login')->with('error', 'Invalid credentials.');
        }

        $roles = (new UserRoleModel())->getRoleKeysForUser((int)$user['id']);

        session()->set([
            'auth_user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'roles' => $roles,
            ],
        ]);

        if (in_array('admin', $roles, true)) return redirect()->to('/admin');
        if (in_array('editor', $roles, true)) return redirect()->to('/editor');
        if (in_array('reviewer', $roles, true)) return redirect()->to('/reviewer');
        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->remove('auth_user');
        session()->destroy();
        return redirect()->to('/login');
    }
}
