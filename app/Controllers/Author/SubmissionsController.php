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
        $uid = (int)($auth['id'] ?? 0);

        $items = $db->table('submissions')
            ->where('submitter_user_id', $uid)
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        return view('author/submissions/index', [
            'title' => 'My Submissions',
            'items' => $items,
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }

    public function new()
    {
        $db = \Config\Database::connect();

        return view('author/submissions/form', [
            'title' => 'New Submission',
            'journals' => $db->table('journals')->orderBy('name','ASC')->get()->getResultArray(),
            'confs' => $db->table('conferences')->orderBy('start_date','DESC')->get()->getResultArray(),
            'error' => session('error'),
            'old' => session('_ci_old_input') ?? [],
        ]);
    }

    public function create()
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $uid = (int)($auth['id'] ?? 0);

        $type = trim((string)$this->request->getPost('type'));
        $title = trim((string)$this->request->getPost('title'));
        $abstract = trim((string)$this->request->getPost('abstract'));
        $keywords = trim((string)$this->request->getPost('keywords'));
        $track = trim((string)$this->request->getPost('track'));
        $journalId = (int)$this->request->getPost('journal_id');
        $conferenceId = (int)$this->request->getPost('conference_id');
        $authorNote = trim((string)$this->request->getPost('author_note'));

        if (!in_array($type, ['journal','conference'], true)) {
            return redirect()->back()->withInput()->with('error','Select submission type.');
        }
        if ($title === '') {
            return redirect()->back()->withInput()->with('error','Title is required.');
        }
        if ($abstract === '') {
            return redirect()->back()->withInput()->with('error','Abstract is required.');
        }
        if ($type === 'journal' && $journalId <= 0) {
            return redirect()->back()->withInput()->with('error','Select a journal.');
        }
        if ($type === 'conference' && $conferenceId <= 0) {
            return redirect()->back()->withInput()->with('error','Select a conference.');
        }

        $file = $this->request->getFile('manuscript');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->withInput()->with('error','Manuscript file is required.');
        }

        $ext = strtolower((string)$file->getClientExtension());
        if (!in_array($ext, ['pdf','doc','docx'], true)) {
            return redirect()->back()->withInput()->with('error','Manuscript must be PDF, DOC, or DOCX.');
        }

        $now = date('Y-m-d H:i:s');

        $db->transStart();

        $db->table('submissions')->insert([
            'type' => $type,
            'journal_id' => ($type === 'journal' ? $journalId : null),
            'conference_id' => ($type === 'conference' ? $conferenceId : null),
            'track' => ($track !== '' ? $track : null),
            'title' => $title,
            'abstract' => $abstract,
            'keywords' => ($keywords !== '' ? $keywords : null),
            'submitter_user_id' => $uid,
            'status' => 'submitted',
            'current_version_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $submissionId = (int)$db->insertID();
        if ($submissionId <= 0) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error','Failed to create submission.');
        }

        $versionId = $this->insertVersion($db, $submissionId, 1, $file, null, $authorNote);
        if ($versionId <= 0) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error','Upload failed.');
        }

        $db->table('submissions')->where('id', $submissionId)->update([
            'current_version_id' => $versionId,
            'updated_at' => $now,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error','Failed to save submission.');
        }

        return redirect()->to('/author/submissions/'.$submissionId)->with('flash','Submission created.');
    }

    public function show(int $id)
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $uid = (int)($auth['id'] ?? 0);

        $sub = $db->table('submissions')->where('id', $id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        if ((int)$sub['submitter_user_id'] !== $uid) {
            return redirect()->to('/author/submissions')->with('error','Access denied.');
        }

        $versions = $db->table('submission_versions')
            ->where('submission_id', $id)
            ->orderBy('version_no','DESC')
            ->get()->getResultArray();

        $timeline = $this->timeline($db, $id, $sub);

        return view('author/submissions/show', [
            'title' => 'Submission #'.$id,
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
        $uid = (int)($auth['id'] ?? 0);

        $sub = $db->table('submissions')->where('id', $id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');
        if ((int)$sub['submitter_user_id'] !== $uid) return redirect()->to('/author/submissions')->with('error','Access denied.');

        $file = $this->request->getFile('manuscript');
        if (!$file || !$file->isValid()) {
            return redirect()->to('/author/submissions/'.$id)->with('error','Choose a valid file.');
        }

        $ext = strtolower((string)$file->getClientExtension());
        if (!in_array($ext, ['pdf','doc','docx'], true)) {
            return redirect()->to('/author/submissions/'.$id)->with('error','Manuscript must be PDF, DOC, or DOCX.');
        }

        $authorNote = trim((string)$this->request->getPost('author_note'));

        $row = $db->table('submission_versions')
            ->select('MAX(version_no) AS mx')
            ->where('submission_id', $id)
            ->get()->getRowArray();

        $next = ((int)($row['mx'] ?? 0)) + 1;

        $db->transStart();

        $versionId = $this->insertVersion($db, $id, $next, $file, null, $authorNote);
        if ($versionId <= 0) {
            $db->transRollback();
            return redirect()->to('/author/submissions/'.$id)->with('error','Upload failed.');
        }

        $db->table('submissions')->where('id', $id)->update([
            'current_version_id' => $versionId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        return redirect()->to('/author/submissions/'.$id)->with('flash','New version uploaded.');
    }

    public function download(int $submissionId, int $versionId)
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $uid = (int)($auth['id'] ?? 0);

        $sub = $db->table('submissions')->where('id', $submissionId)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');
        if ((int)$sub['submitter_user_id'] !== $uid) return redirect()->to('/author/submissions')->with('error','Access denied.');

        $ver = $db->table('submission_versions')->where('id', $versionId)->get()->getRowArray();
        if (!$ver || (int)$ver['submission_id'] !== $submissionId) throw PageNotFoundException::forPageNotFound('Version not found');
        if (empty($ver['manuscript_path'])) throw PageNotFoundException::forPageNotFound('No file');

        $path = WRITEPATH . 'uploads/' . ltrim($ver['manuscript_path'], '/\\');
        if (!is_file($path)) throw PageNotFoundException::forPageNotFound('File missing');

        return $this->response->download($path, null);
    }

    private function insertVersion($db, int $submissionId, int $versionNo, $file, ?string $suppRel, ?string $note): int
    {
        $baseDir = WRITEPATH.'uploads/submissions/'.$submissionId.'/v'.$versionNo;
        if (!is_dir($baseDir)) @mkdir($baseDir, 0775, true);

        $client = (string)$file->getClientName();
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '_', $client);
        $safe = trim((string)$safe, '_');
        if ($safe === '' || $safe === '.' || $safe === '..') {
            $safe = 'manuscript.'.strtolower((string)$file->getClientExtension());
        }
        if (is_file($baseDir.'/'.$safe)) $safe = time().'_'.$safe;

        if (!$file->move($baseDir, $safe)) return 0;

        $manRel = 'submissions/'.$submissionId.'/v'.$versionNo.'/'.$safe;

        $db->table('submission_versions')->insert([
            'submission_id' => $submissionId,
            'version_no' => $versionNo,
            'manuscript_path' => $manRel,
            'supplementary_path' => $suppRel,
            'author_note' => ($note !== '' ? $note : null),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int)$db->insertID();
    }

    private function timeline($db, int $submissionId, array $sub): array
    {
        $steps = [];

        $steps[] = ['label'=>'Submitted', 'done'=>true, 'at'=>$sub['created_at'] ?? null];
        $steps[] = ['label'=>'Under review', 'done'=>in_array($sub['status'], ['under_review','decided','accepted','rejected','revision','published'], true), 'at'=>null];
        $steps[] = ['label'=>'Decision recorded', 'done'=>$db->table('decisions')->where('submission_id',$submissionId)->countAllResults() > 0, 'at'=>null];
        $steps[] = ['label'=>'Published', 'done'=>$db->table('publications')->where('submission_id',$submissionId)->countAllResults() > 0, 'at'=>null];

        return $steps;
    }
}
