<?php

namespace App\Controllers\Author;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $userId = (int)($auth['id'] ?? 0);

        // If submissions has an owner column, we will use it to scope the list.
        [$ownerCol, $scopeNote] = $this->detectSubmissionOwnerColumn();

        $q = $db->table('submissions')->orderBy('id', 'DESC')->limit(20);
        if ($ownerCol !== null && $userId > 0) {
            $q->where($ownerCol, $userId);
        }

        $items = $q->get()->getResultArray();

        return view('author/dashboard', [
            'title' => 'Author Dashboard',
            'items' => $items,
            'scopeNote' => $scopeNote,
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }

    /**
     * @return array{0:?string,1:?string} [ownerColumn, scopeNote]
     */
    private function detectSubmissionOwnerColumn(): array
    {
        $db = \Config\Database::connect();
        $cols = $db->getFieldNames('submissions');

        $candidates = [
            'author_id',
            'user_id',
            'created_by',
            'submitted_by',
        ];

        foreach ($candidates as $c) {
            if (in_array($c, $cols, true)) {
                return [$c, null];
            }
        }

        return [null, 'Note: Could not detect a submissions ownership column (e.g. author_id/user_id). Showing latest submissions without user scoping.'];
    }
}
