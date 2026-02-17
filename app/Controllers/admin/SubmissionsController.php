<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class SubmissionsController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $status = trim((string)request()->getGet('status'));

        $q = $db->table('submissions')->orderBy('id','DESC');
        if ($status !== '') $q->where('status', $status);

        return view('admin/submissions/index', [
            'title' => 'Submissions',
            'items' => $q->get()->getResultArray(),
            'status' => $status,
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }

    public function show(int $id)
    {
        $db = \Config\Database::connect();

        $sub = $db->table('submissions')->where('id',$id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        $versions = $db->table('submission_versions')->where('submission_id',$id)->orderBy('version_no','DESC')->get()->getResultArray();
        $pub = $db->table('publications')->where('submission_id',$id)->get()->getRowArray();

        return view('admin/submissions/show', [
            'title' => 'Submission #'.$id,
            'sub' => $sub,
            'versions' => $versions,
            'publication' => $pub,
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }

    public function publish(int $id)
    {
        $db = \Config\Database::connect();

        $sub = $db->table('submissions')->where('id',$id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        // prevent double publish
        if ($db->table('publications')->where('submission_id',$id)->countAllResults() > 0) {
            return redirect()->to('/admin/submissions/'.$id)->with('error','Already published.');
        }

        $volume = trim((string)$this->request->getPost('volume'));
        $issue  = trim((string)$this->request->getPost('issue'));
        $pages  = trim((string)$this->request->getPost('pages'));
        $doi    = trim((string)$this->request->getPost('doi'));

        $now = date('Y-m-d H:i:s');

        $db->transStart();

        $db->table('publications')->insert([
            'submission_id' => $id,
            'published_at' => $now,
            'volume' => ($volume !== '' ? $volume : null),
            'issue' => ($issue !== '' ? $issue : null),
            'pages' => ($pages !== '' ? $pages : null),
            'doi' => ($doi !== '' ? $doi : null),
            'citation_json' => null,
        ]);

        $pubId = (int)$db->insertID();

        $db->table('submissions')->where('id',$id)->update([
            'status' => 'published',
            'updated_at' => $now,
        ]);

        // Issue publication certificate (REQUIRED)
        $code = 'AIRN-PUB-'.$id.'-'.substr(bin2hex(random_bytes(8)),0,8);

        $db->table('certificate_issuances')->insert([
            'user_id' => (int)$sub['submitter_user_id'],
            'type' => 'publication',
            'submission_id' => $id,
            'publication_id' => $pubId,
            'code' => $code,
            'issued_at' => $now,
            'pdf_path' => null,
            'meta_json' => null,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/submissions/'.$id)->with('error','Publish failed.');
        }

        return redirect()->to('/admin/submissions/'.$id)->with('flash','Published. Certificate issued.');
    }
}
