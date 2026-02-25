<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserRoleModel;

class AuthController extends BaseController
{
    public function registerForm()
    {
        return view('auth/register', [
            'error' => session('error'),
            'old'   => session('old') ?? [],
        ]);
    }

    public function register()
    {
        $name  = trim((string) $this->request->getPost('name'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $p1    = (string) $this->request->getPost('password');
        $p2    = (string) $this->request->getPost('password2');

        $old = ['name' => $name, 'email' => $email];

        if ($name === '' || $email === '' || $p1 === '' || $p2 === '') {
            return redirect()->to('/register')->with('error', 'All fields are required.')->with('old', $old);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('/register')->with('error', 'Enter a valid email address.')->with('old', $old);
        }

        if (strlen($p1) < 8) {
            return redirect()->to('/register')->with('error', 'Password must be at least 8 characters.')->with('old', $old);
        }

        if ($p1 !== $p2) {
            return redirect()->to('/register')->with('error', 'Passwords do not match.')->with('old', $old);
        }

        $users = new UserModel();
        $existing = $users->where('email', $email)->first();
        if ($existing) {
            return redirect()->to('/register')->with('error', 'An account with this email already exists.')->with('old', $old);
        }

        $now = date('Y-m-d H:i:s');
        $users->insert([
            'name'          => $name,
            'email'         => $email,
            'password_hash' => password_hash($p1, PASSWORD_DEFAULT),
            'status'        => 'active',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $userId = (int) $users->getInsertID();
        if ($userId <= 0) {
            return redirect()->to('/register')->with('error', 'Registration failed. Try again.')->with('old', $old);
        }

        // Assign AUTHOR role (must exist in roles table)
        $urm = new \App\Models\UserRoleModel();
        $roleId = $urm->getRoleIdByKey('author');

        if (!$roleId) {
            return redirect()->to('/register')->with('error', 'Author role not found.')->with('old', $old);
        }

        // // Insert only if not already assigned (composite PK user_id+role_id)
        // $exists = db_connect()
        //     ->table('user_roles')
        //     ->where('user_id', $userId)
        //     ->where('role_id', $roleId)
        //     ->countAllResults();

        // if (!$exists) {
        //     db_connect()->table('user_roles')->insert([
        //         'user_id'    => $userId,
        //         'role_id'    => $roleId,
        //         'created_at' => date('Y-m-d H:i:s'),
        //     ]);
        // }
        $userRoleModel = new \App\Models\UserRoleModel();

        $roleId = $userRoleModel->getRoleIdByKey('author');
        if (!$roleId) {
            return redirect()->back()->withInput()->with('error', 'Author role not found.');
        }

        $userRoleModel->insert([
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/login')->with('error', 'Account created. Please login.');
    }

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
