<?php

namespace App\Controllers\Author;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class SubmissionsController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $userId = (int)($auth['id'] ?? 0);

        [$ownerCol, $scopeNote] = $this->detectSubmissionOwnerColumn();

        $q = $db->table('submissions')->orderBy('id', 'DESC');
        if ($ownerCol !== null && $userId > 0) {
            $q->where($ownerCol, $userId);
        }

        $items = $q->get()->getResultArray();

        return view('author/submissions/index', [
            'title' => 'My Submissions',
            'items' => $items,
            'scopeNote' => $scopeNote,
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }

    public function new()
    {
        $db = \Config\Database::connect();

        $journals = $db->table('journals')->orderBy('name', 'ASC')->get()->getResultArray();
        $confs = $db->table('conferences')->orderBy('start_date', 'DESC')->get()->getResultArray();

        return view('author/submissions/form', [
            'title' => 'New Submission',
            'journals' => $journals,
            'confs' => $confs,
            'error' => session('error'),
            'old' => session('_ci_old_input') ?? [],
        ]);
    }

    public function create()
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $userId = (int)($auth['id'] ?? 0);

        $type = trim((string)$this->request->getPost('type'));
        $title = trim((string)$this->request->getPost('title'));
        $abstract = trim((string)$this->request->getPost('abstract'));
        $keywords = trim((string)$this->request->getPost('keywords'));
        $track = trim((string)$this->request->getPost('track'));
        $journalId = (int)$this->request->getPost('journal_id');
        $conferenceId = (int)$this->request->getPost('conference_id');

        if (!in_array($type, ['journal', 'conference'], true)) {
            return redirect()->back()->withInput()->with('error', 'Please select submission type.');
        }
        if ($title === '') {
            return redirect()->back()->withInput()->with('error', 'Title is required.');
        }
        if ($type === 'journal' && $journalId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Please select a journal.');
        }
        if ($type === 'conference' && $conferenceId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Please select a conference.');
        }

        $file = $this->request->getFile('manuscript');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Manuscript file is required.');
        }

        // Soft validation: allow PDF/DOC/DOCX.
        $ext = strtolower((string)$file->getClientExtension());
        if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
            return redirect()->back()->withInput()->with('error', 'Manuscript must be PDF, DOC, or DOCX.');
        }

        $subCols = $db->getFieldNames('submissions');
        [$ownerCol, $_scopeNote] = $this->detectSubmissionOwnerColumn();

        $insert = [];
        $this->maybeSet($insert, $subCols, 'type', $type);
        $this->maybeSet($insert, $subCols, 'title', $title);
        $this->maybeSet($insert, $subCols, 'abstract', ($abstract !== '' ? $abstract : null));
        $this->maybeSet($insert, $subCols, 'keywords', ($keywords !== '' ? $keywords : null));
        $this->maybeSet($insert, $subCols, 'track', ($track !== '' ? $track : null));
        $this->maybeSet($insert, $subCols, 'journal_id', ($type === 'journal' ? $journalId : null));
        $this->maybeSet($insert, $subCols, 'conference_id', ($type === 'conference' ? $conferenceId : null));
        $this->maybeSet($insert, $subCols, 'current_version_id', null);
        if ($ownerCol !== null && $userId > 0) {
            $this->maybeSet($insert, $subCols, $ownerCol, $userId);
        }
        $now = date('Y-m-d H:i:s');
        $this->maybeSet($insert, $subCols, 'created_at', $now);
        $this->maybeSet($insert, $subCols, 'updated_at', $now);

        $db->transStart();

        $db->table('submissions')->insert($insert);
        $submissionId = (int)$db->insertID();
        if ($submissionId <= 0) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create submission.');
        }

        $versionId = $this->createFirstVersion($db, $submissionId, $file);
        if ($versionId <= 0) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Submission created, but manuscript upload failed (no version row inserted).');
        }

        // Update submissions.current_version_id if column exists.
        if (in_array('current_version_id', $subCols, true)) {
            $db->table('submissions')->where('id', $submissionId)->update([
                'current_version_id' => $versionId,
                ...(in_array('updated_at', $subCols, true) ? ['updated_at' => $now] : []),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save submission.');
        }

        return redirect()->to('/author/submissions/' . $submissionId)->with('flash', 'Submission created and manuscript uploaded.');
    }

    public function show(int $id)
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $userId = (int)($auth['id'] ?? 0);

        $sub = $db->table('submissions')->where('id', $id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        // Ownership check if an owner column exists.
        [$ownerCol, $_scopeNote] = $this->detectSubmissionOwnerColumn();
        if ($ownerCol !== null && $userId > 0 && isset($sub[$ownerCol]) && (int)$sub[$ownerCol] !== $userId) {
            // Allow admins/editors/reviewers too since your author filter allows those roles.
            // Only block if user is a pure author.
            $roles = (array)($auth['roles'] ?? []);
            $privileged = array_intersect($roles, ['admin', 'editor', 'reviewer']);
            if (empty($privileged)) {
                return redirect()->to('/author/submissions')->with('error', 'Access denied.');
            }
        }

        $versions = $this->getVersionsForSubmission($db, $id);
        $timeline = $this->buildTimeline($db, $id, $sub);

        return view('author/submissions/show', [
            'title' => 'Submission #' . $id,
            'sub' => $sub,
            'versions' => $versions,
            'timeline' => $timeline,
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }

    public function upload(int $id)
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $userId = (int)($auth['id'] ?? 0);

        $sub = $db->table('submissions')->where('id', $id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        [$ownerCol, $_scopeNote] = $this->detectSubmissionOwnerColumn();
        if ($ownerCol !== null && $userId > 0 && isset($sub[$ownerCol]) && (int)$sub[$ownerCol] !== $userId) {
            $roles = (array)($auth['roles'] ?? []);
            $privileged = array_intersect($roles, ['admin', 'editor', 'reviewer']);
            if (empty($privileged)) {
                return redirect()->to('/author/submissions/' . $id)->with('error', 'Access denied.');
            }
        }

        $file = $this->request->getFile('manuscript');
        if (!$file || !$file->isValid()) {
            return redirect()->to('/author/submissions/' . $id)->with('error', 'Please choose a valid file.');
        }

        $ext = strtolower((string)$file->getClientExtension());
        if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
            return redirect()->to('/author/submissions/' . $id)->with('error', 'Manuscript must be PDF, DOC, or DOCX.');
        }

        $subCols = $db->getFieldNames('submissions');
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        $versionId = $this->createNextVersion($db, $id, $file);
        if ($versionId <= 0) {
            $db->transRollback();
            return redirect()->to('/author/submissions/' . $id)->with('error', 'Upload failed (no version row inserted).');
        }

        if (in_array('current_version_id', $subCols, true)) {
            $db->table('submissions')->where('id', $id)->update([
                'current_version_id' => $versionId,
                ...(in_array('updated_at', $subCols, true) ? ['updated_at' => $now] : []),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/author/submissions/' . $id)->with('error', 'Upload failed.');
        }

        return redirect()->to('/author/submissions/' . $id)->with('flash', 'New manuscript version uploaded.');
    }

    public function download(int $submissionId, int $versionId)
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $userId = (int)($auth['id'] ?? 0);

        $sub = $db->table('submissions')->where('id', $submissionId)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        [$ownerCol, $_scopeNote] = $this->detectSubmissionOwnerColumn();
        if ($ownerCol !== null && $userId > 0 && isset($sub[$ownerCol]) && (int)$sub[$ownerCol] !== $userId) {
            $roles = (array)($auth['roles'] ?? []);
            $privileged = array_intersect($roles, ['admin', 'editor', 'reviewer']);
            if (empty($privileged)) {
                return redirect()->to('/author/submissions/' . $submissionId)->with('error', 'Access denied.');
            }
        }

        $ver = $db->table('submission_versions')
            ->where('id', $versionId)
            ->get()
            ->getRowArray();
        if (!$ver) throw PageNotFoundException::forPageNotFound('Version not found');

        // If submission_id exists on versions, enforce it.
        $vCols = $db->getFieldNames('submission_versions');
        if (in_array('submission_id', $vCols, true) && (int)($ver['submission_id'] ?? 0) !== $submissionId) {
            throw PageNotFoundException::forPageNotFound('Version not found');
        }

        if (empty($ver['manuscript_path'])) throw PageNotFoundException::forPageNotFound('No file');
        $path = WRITEPATH . 'uploads/' . ltrim($ver['manuscript_path'], '/\\');
        if (!is_file($path)) throw PageNotFoundException::forPageNotFound('File missing');

        return $this->response->download($path, null);
    }

    // -----------------
    // Internals
    // -----------------

    /**
     * @return array{0:?string,1:?string} [ownerColumn, scopeNote]
     */
    private function detectSubmissionOwnerColumn(): array
    {
        $db = \Config\Database::connect();
        $cols = $db->getFieldNames('submissions');

        $candidates = ['author_id', 'user_id', 'created_by', 'submitted_by'];
        foreach ($candidates as $c) {
            if (in_array($c, $cols, true)) {
                return [$c, null];
            }
        }

        return [null, 'Note: Could not detect a submissions ownership column (e.g. author_id/user_id). Showing submissions without user scoping.'];
    }

    private function maybeSet(array &$data, array $cols, string $key, $value): void
    {
        if (in_array($key, $cols, true)) {
            $data[$key] = $value;
        }
    }

    private function createFirstVersion($db, int $submissionId, $file): int
    {
        return $this->insertVersionRow($db, $submissionId, 1, $file);
    }

    private function createNextVersion($db, int $submissionId, $file): int
    {
        $vCols = $db->getFieldNames('submission_versions');
        $versionNo = 1;

        if (in_array('submission_id', $vCols, true) && in_array('version_no', $vCols, true)) {
            $row = $db->table('submission_versions')
                ->select('MAX(version_no) AS mx')
                ->where('submission_id', $submissionId)
                ->get()
                ->getRowArray();
            $versionNo = ((int)($row['mx'] ?? 0)) + 1;
        }

        return $this->insertVersionRow($db, $submissionId, $versionNo, $file);
    }

    private function insertVersionRow($db, int $submissionId, int $versionNo, $file): int
    {
        $vCols = $db->getFieldNames('submission_versions');

        if (!in_array('manuscript_path', $vCols, true)) {
            return 0;
        }

        // We can still function without submission_id/version_no columns, but will prefer them when available.
        $baseDir = WRITEPATH . 'uploads/submissions/' . $submissionId . '/v' . $versionNo;
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0775, true);
        }

        $clientName = (string)$file->getClientName();
        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $clientName);
        $safeName = trim((string)$safeName, '_');
        if ($safeName === '' || $safeName === '.' || $safeName === '..') {
            $safeName = 'manuscript.' . strtolower((string)$file->getClientExtension());
        }

        // Avoid collisions.
        $targetPath = $baseDir . '/' . $safeName;
        if (is_file($targetPath)) {
            $safeName = time() . '_' . $safeName;
        }

        if (!$file->move($baseDir, $safeName)) {
            return 0;
        }

        $relPath = 'submissions/' . $submissionId . '/v' . $versionNo . '/' . $safeName;
        $now = date('Y-m-d H:i:s');

        $insert = [];
        $this->maybeSet($insert, $vCols, 'submission_id', $submissionId);
        $this->maybeSet($insert, $vCols, 'version_no', $versionNo);
        $this->maybeSet($insert, $vCols, 'manuscript_path', $relPath);
        $this->maybeSet($insert, $vCols, 'created_at', $now);
        $this->maybeSet($insert, $vCols, 'updated_at', $now);

        $db->table('submission_versions')->insert($insert);
        return (int)$db->insertID();
    }

    private function getVersionsForSubmission($db, int $submissionId): array
    {
        $vCols = $db->getFieldNames('submission_versions');
        $q = $db->table('submission_versions');

        if (in_array('submission_id', $vCols, true)) {
            $q->where('submission_id', $submissionId);
        }

        if (in_array('version_no', $vCols, true)) {
            $q->orderBy('version_no', 'DESC');
        } else {
            $q->orderBy('id', 'DESC');
        }

        return $q->get()->getResultArray();
    }

    private function buildTimeline($db, int $submissionId, array $sub): array
    {
        $steps = [];

        // 1) Created
        $steps[] = [
            'label' => 'Submission created',
            'done'  => true,
            'at'    => $sub['created_at'] ?? null,
        ];

        // 2) Manuscript uploaded
        $hasVersion = false;
        $vCols = $db->getFieldNames('submission_versions');
        if (in_array('submission_id', $vCols, true)) {
            $hasVersion = (bool)$db->table('submission_versions')->where('submission_id', $submissionId)->countAllResults();
        } else {
            $hasVersion = !empty($sub['current_version_id']);
        }

        $steps[] = [
            'label' => 'Manuscript uploaded',
            'done'  => $hasVersion,
            'at'    => null,
        ];

        // Best-effort workflow signals (only if table exists and has submission_id)
        $steps[] = $this->timelineFromTable($db, 'review_assignments', $submissionId, 'Review assigned');
        $steps[] = $this->timelineFromTable($db, 'reviews', $submissionId, 'Reviews submitted');
        $steps[] = $this->timelineFromTable($db, 'decisions', $submissionId, 'Editorial decision');

        // Published
        $published = false;
        if ($this->tableExists($db, 'publications')) {
            $pCols = $db->getFieldNames('publications');
            if (in_array('submission_id', $pCols, true)) {
                $published = (bool)$db->table('publications')->where('submission_id', $submissionId)->countAllResults();
            }
        }
        $steps[] = [
            'label' => 'Published',
            'done'  => $published,
            'at'    => null,
        ];

        return $steps;
    }

    private function timelineFromTable($db, string $table, int $submissionId, string $label): array
    {
        if (!$this->tableExists($db, $table)) {
            return ['label' => $label, 'done' => false, 'at' => null];
        }

        $cols = $db->getFieldNames($table);
        if (!in_array('submission_id', $cols, true)) {
            return ['label' => $label, 'done' => false, 'at' => null];
        }

        $done = (bool)$db->table($table)->where('submission_id', $submissionId)->countAllResults();
        return ['label' => $label, 'done' => $done, 'at' => null];
    }

    private function tableExists($db, string $table): bool
    {
        try {
            return in_array($table, $db->listTables(), true);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
