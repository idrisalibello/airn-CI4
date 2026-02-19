<?php

namespace App\Controllers\Author;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $uid = (int)($auth['id'] ?? 0);

        // Counts
        $total = $db->table('submissions')->where('submitter_user_id', $uid)->countAllResults();

        $byStatusRows = $db->table('submissions')
            ->select('status, COUNT(*) AS c')
            ->where('submitter_user_id', $uid)
            ->groupBy('status')
            ->get()->getResultArray();

        $byStatus = [];
        foreach ($byStatusRows as $r) {
            $byStatus[(string)$r['status']] = (int)$r['c'];
        }

        // Recent submissions
        $items = $db->table('submissions')
            ->where('submitter_user_id', $uid)
            ->orderBy('id', 'DESC')
            ->limit(20)
            ->get()->getResultArray();

        return view('author/dashboard', [
            'title' => 'Author Dashboard',
            'total' => $total,
            'byStatus' => $byStatus,
            'items' => $items,
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }
}
