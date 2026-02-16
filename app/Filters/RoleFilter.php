<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = session('auth_user');
        if (!$auth) {
            return redirect()->to('/login');
        }

        $roles = $auth['roles'] ?? [];
        $required = $arguments ?? [];

        foreach ($required as $r) {
            if (in_array($r, $roles, true)) {
                return null;
            }
        }

        return redirect()->to('/login')->with('error', 'Access denied.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
