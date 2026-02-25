<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;

class SiteController extends BaseController
{
    public function home()
    {
        return view('site/home', [
            'title' => 'Academic & International Research Network',
        ]);
    }

    public function journals()
    {
        $db = \Config\Database::connect();
        $items = $db->table('journals')->orderBy('id', 'DESC')->get()->getResultArray();

        return view('site/journals_list', [
            'title' => 'Journals',
            'items' => $items,
        ]);
    }

    public function journal(string $slug)
    {
        $db = \Config\Database::connect();
        $journal = $db->table('journals')->where('slug', $slug)->get()->getRowArray();
        if (!$journal) throw PageNotFoundException::forPageNotFound('Journal not found');

        // latest published in this journal
        $published = $db->table('publications p')
            ->select('p.id AS publication_id, p.published_at, s.id AS submission_id, s.title')
            ->join('submissions s', 's.id = p.submission_id')
            ->where('s.type', 'journal')
            ->where('s.journal_id', (int)$journal['id'])
            ->orderBy('p.published_at', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return view('site/journal_detail', [
            'title' => $journal['name'],
            'journal' => $journal,
            'published' => $published,
        ]);
    }

    public function conferences()
    {
        $db = \Config\Database::connect();
        $items = $db->table('conferences')->orderBy('start_date', 'DESC')->get()->getResultArray();

        foreach ($items as &$c) {
            $settings = [];
            if (!empty($c['settings_json'])) {
                $tmp = json_decode((string)$c['settings_json'], true);
                if (is_array($tmp)) $settings = $tmp;
            }
            $c['theme'] = trim((string)($settings['theme'] ?? ''));
            $c['announcement'] = trim((string)($settings['announcement'] ?? ''));
        }
        unset($c);

        return view('site/conferences_list', [
            'title' => 'Conferences',
            'items' => $items,
        ]);
    }

    public function conference(string $slug)
    {
        $db = \Config\Database::connect();
        $conf = $db->table('conferences')->where('slug', $slug)->get()->getRowArray();
        if (!$conf) throw PageNotFoundException::forPageNotFound('Conference not found');

        $settings = [];
        if (!empty($conf['settings_json'])) {
            $tmp = json_decode((string)$conf['settings_json'], true);
            if (is_array($tmp)) $settings = $tmp;
        }
        $conf['theme'] = trim((string)($settings['theme'] ?? ''));
        $conf['announcement'] = trim((string)($settings['announcement'] ?? ''));

        $published = $db->table('publications p')
            ->select('p.id AS publication_id, p.published_at, s.id AS submission_id, s.title, s.track')
            ->join('submissions s', 's.id = p.submission_id')
            ->where('s.type', 'conference')
            ->where('s.conference_id', (int)$conf['id'])
            ->orderBy('p.published_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        return view('site/conference_detail', [
            'title' => $conf['name'],
            'conf' => $conf,
            'published' => $published,
        ]);
    }

    public function published()
    {
        $db = \Config\Database::connect();
        $rows = $db->table('publications p')
            ->select('p.id AS publication_id, p.published_at, p.volume, p.issue, p.doi, s.id AS submission_id, s.title, s.type')
            ->join('submissions s', 's.id = p.submission_id')
            ->orderBy('p.published_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        return view('site/published_list', [
            'title' => 'Published',
            'items' => $rows,
        ]);
    }

    public function publication(int $id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('publications p')
            ->select('p.*, s.id AS submission_id, s.title, s.abstract, s.keywords, s.type, s.journal_id, s.conference_id, s.track')
            ->join('submissions s', 's.id = p.submission_id')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();

        if (!$row) throw PageNotFoundException::forPageNotFound('Publication not found');

        return view('site/publication_detail', [
            'title' => $row['title'],
            'item' => $row,
        ]);
    }

    public function download(int $submissionId)
    {
        $db = \Config\Database::connect();

        // allow download only if published
        $pub = $db->table('publications')->where('submission_id', $submissionId)->get()->getRowArray();
        if (!$pub) throw PageNotFoundException::forPageNotFound('Not published');

        $sub = $db->table('submissions')->where('id', $submissionId)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        // Prefer final published PDF (auto-generated) if present
        $pubPath = (string)($pub['published_file_path'] ?? '');
        if ($pubPath !== '') {
            $absPub = WRITEPATH . ltrim($pubPath, '/\\');
            if (is_file($absPub)) {
                return $this->response->download($absPub, null);
            }
        }

        // Fallback: manuscript (legacy)
        if (empty($sub['current_version_id'])) throw PageNotFoundException::forPageNotFound('No file');
        $ver = $db->table('submission_versions')->where('id', (int)$sub['current_version_id'])->get()->getRowArray();
        if (!$ver || empty($ver['manuscript_path'])) throw PageNotFoundException::forPageNotFound('No file');

        $path = WRITEPATH . 'uploads/' . ltrim($ver['manuscript_path'], '/\\');
        if (!is_file($path)) throw PageNotFoundException::forPageNotFound('File missing');

        return $this->response->download($path, null);
    }

    public function about()
    {
        return view('site/static', ['title' => 'About', 'heading' => 'About AIRN']);
    }
    public function contact()
    {
        return view('site/static', ['title' => 'Contact', 'heading' => 'Contact']);
    }
    public function policies()
    {
        return view('site/static', ['title' => 'Policies', 'heading' => 'Policies']);
    }
    public function verifyCertificate(string $code)
    {
        $db = \Config\Database::connect();

        $cert = $db->table('certificate_issuances')->where('code', $code)->get()->getRowArray();
        if (!$cert) {
            return view('site/certificate_verify', ['title' => 'Certificate Verification', 'valid' => false]);
        }

        $user = $db->table('users')->where('id', (int)$cert['user_id'])->get()->getRowArray();
        $sub  = $db->table('submissions')->where('id', (int)$cert['submission_id'])->get()->getRowArray();
        $pub  = $db->table('publications')->where('id', (int)$cert['publication_id'])->get()->getRowArray();

        return view('site/certificate_verify', [
            'title' => 'Certificate Verification',
            'valid' => true,
            'cert' => $cert,
            'user' => $user,
            'submission' => $sub,
            'publication' => $pub,
        ]);
    }



    public function cameraReadyTemplate()
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('publications/article_pdf', [
            // Fill with a sample dataset so the PDF is a true “approved template”
            'journal_name' => 'AIRN Journal of Computing Systems',
            'volume' => '4',
            'issue' => '1',
            'year' => '2026',
            'received_at' => '12 Jan 2026',
            'accepted_at' => '28 Feb 2026',
            'published_at' => '10 Mar 2026',
            'article_id' => 'AIRN-2026-041-002',
            'doi' => '10.1234/airn.v4i1.002',
            'title' => 'Hybrid Optimization Techniques for Adaptive Network Routing in MANET Environments',
            'authors_line' => 'Idris Bello<sup>1*</sup>, Amina Yusuf<sup>2</sup>, Chukwuemeka Okoye<sup>3</sup>',
            'affiliations_html' =>
            '<div><sup>1</sup>Department of Computer Science, XYZ University, Nigeria</div>' .
                '<div><sup>2</sup>Faculty of Engineering, ABC Institute, Ghana</div>' .
                '<div><sup>3</sup>School of Computing, QRS University, Kenya</div>',
            'corresponding_html' => '*Corresponding author: idris@xyz.edu &nbsp;&nbsp; ORCID: 0000-0002-XXXX-XXXX',
            'abstract' => 'This study proposes an advanced hybrid optimization model...',
            'keywords' => 'MANET, hybrid optimization, adaptive routing, genetic algorithms',
            // Keep body minimal; this endpoint is a TEMPLATE
            'content_html' => '<h2>1. Introduction</h2><p>Replace with your full paper content...</p>',
        ]));

        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="camera_ready_template.pdf"')
            ->setBody($dompdf->output());
    }
}
