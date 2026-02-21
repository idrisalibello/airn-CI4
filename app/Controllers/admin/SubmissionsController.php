<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;

class SubmissionsController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $status = trim((string)request()->getGet('status'));

        $q = $db->table('submissions')->orderBy('id', 'DESC');
        if ($status !== '') $q->where('status', $status);

        return view('admin/submissions/index', [
            'title' => 'Submissions',
            'items' => $q->get()->getResultArray(),
            'status' => $status,
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }

    public function present(int $id)
    {
        $db = \Config\Database::connect();

        $sub = $db->table('submissions')->where('id', $id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        if ((string)($sub['type'] ?? '') !== 'conference') {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Presentation certificates apply to conference submissions only.');
        }

        // Gate: must be accepted (by status OR latest decision)
        $latestDecision = $db->table('decisions')
            ->where('submission_id', $id)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $isAccepted =
            ((string)($sub['status'] ?? '') === 'accepted') ||
            (!empty($latestDecision) && (string)($latestDecision['decision'] ?? '') === 'accept');

        if (!$isAccepted) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Cannot issue presentation certificate: submission not accepted.');
        }

        // Prevent double issue
        $exists = $db->table('certificate_issuances')
            ->where('type', 'presentation')
            ->where('submission_id', $id)
            ->countAllResults();

        if ($exists > 0) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Presentation certificate already issued.');
        }

        $now = date('Y-m-d H:i:s');

        $db->transStart();

        $code = 'AIRN-PRES-' . $id . '-' . substr(bin2hex(random_bytes(8)), 0, 8);

        $db->table('certificate_issuances')->insert([
            'user_id'        => (int)$sub['submitter_user_id'],
            'type'           => 'presentation',
            'submission_id'  => $id,
            'publication_id' => null,
            'code'           => $code,
            'issued_at'      => $now,
            'pdf_path'       => null,
            'meta_json'      => null,
        ]);

        $certId = (int)$db->insertID();

        // Context
        $user = $db->table('users')->where('id', (int)$sub['submitter_user_id'])->get()->getRowArray();

        $conf = null;
        if (!empty($sub['conference_id'])) {
            $conf = $db->table('conferences')->where('id', (int)$sub['conference_id'])->get()->getRowArray();
        }

        if (!$user || !$conf) {
            $db->transComplete();
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Certificate context missing (user or conference).');
        }

        $year = (int)date('Y', strtotime((string)($conf['start_date'] ?? $now)));

        $brandRight = 'Conference • ' . $year;

        $verifyUrl = base_url('verify/certificate/' . $code);

        $html = view('certificates/presentation', [
            'brand_left'       => $this->brandLeft($sub),
            'brand_right'      => $brandRight,
            'recipient_name'   => (string)($user['name'] ?? ''),
            'paper_title'      => (string)($sub['title'] ?? ''),
            'conference_name'  => (string)($conf['name'] ?? ''),
            'conference_venue' => (string)($conf['venue'] ?? ''),
            'start_date'       => (string)($conf['start_date'] ?? ''),
            'end_date'         => (string)($conf['end_date'] ?? ''),
            'code'             => $code,
            'verify_url'       => $verifyUrl,
            'verify_short'     => 'airn/verify/' . $code,
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $options = $dompdf->getOptions();
        if (method_exists($options, 'setIsRemoteEnabled')) {
            $options->setIsRemoteEnabled(true);
        }
        $dompdf->setOptions($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dir = WRITEPATH . 'certificates/' . $year;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $rel = 'certificates/' . $year . '/' . $code . '.pdf';
        $abs = WRITEPATH . $rel;

        file_put_contents($abs, $dompdf->output());

        $db->table('certificate_issuances')->where('id', $certId)->update([
            'pdf_path' => $rel,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Issue failed.');
        }

        return redirect()->to('/admin/submissions/' . $id)->with('flash', 'Marked as presented. Certificate issued.');
    }

    public function presentationCertificate(int $id)
    {
        $db = \Config\Database::connect();

        $sub = $db->table('submissions')->where('id', $id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        if ((string)($sub['type'] ?? '') !== 'conference') {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Not a conference submission.');
        }

        $cert = $db->table('certificate_issuances')
            ->where('type', 'presentation')
            ->where('submission_id', $id)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$cert) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Presentation certificate not issued yet.');
        }

        $pdfPathRaw = $cert['pdf_path'] ?? '';
        $pdfPath = is_string($pdfPathRaw) ? $pdfPathRaw : '';
        if ($pdfPath !== '') {
            $absExisting = WRITEPATH . ltrim($pdfPath, '/\\');
            if (is_file($absExisting)) {
                return $this->response->download($absExisting, null);
            }
        }

        // Regenerate (fallback)
        $user = $db->table('users')->where('id', (int)$cert['user_id'])->get()->getRowArray();
        $conf = null;
        if (!empty($sub['conference_id'])) {
            $conf = $db->table('conferences')->where('id', (int)$sub['conference_id'])->get()->getRowArray();
        }

        if (!$user || !$conf) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Certificate context missing.');
        }

        $certCode = (string)($cert['code'] ?? '');
        $year = (int)date('Y', strtotime((string)($conf['start_date'] ?? date('Y-m-d'))));
        $brandRight = 'Conference • ' . $year;

        $verifyUrl = base_url('verify/certificate/' . $certCode);

        $html = view('certificates/presentation', [
            'brand_left'       => $this->brandLeft($sub),
            'brand_right'      => $brandRight,
            'recipient_name'   => (string)($user['name'] ?? ''),
            'paper_title'      => (string)($sub['title'] ?? ''),
            'conference_name'  => (string)($conf['name'] ?? ''),
            'conference_venue' => (string)($conf['venue'] ?? ''),
            'start_date'       => (string)($conf['start_date'] ?? ''),
            'end_date'         => (string)($conf['end_date'] ?? ''),
            'code'             => $certCode,
            'verify_url'       => $verifyUrl,
            'verify_short'     => 'airn/verify/' . $certCode,
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $options = $dompdf->getOptions();
        if (method_exists($options, 'setIsRemoteEnabled')) {
            $options->setIsRemoteEnabled(true);
        }
        $dompdf->setOptions($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dir = WRITEPATH . 'certificates/' . $year;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $rel = 'certificates/' . $year . '/' . $certCode . '.pdf';
        $abs = WRITEPATH . $rel;

        file_put_contents($abs, $dompdf->output());

        $db->table('certificate_issuances')
            ->where('id', (int)$cert['id'])
            ->update(['pdf_path' => $rel]);

        return $this->response->download($abs, null);
    }

    public function show(int $id)
    {
        $db = \Config\Database::connect();

        $sub = $db->table('submissions')->where('id', $id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        $versions = $db->table('submission_versions')
            ->where('submission_id', $id)
            ->orderBy('version_no', 'DESC')
            ->get()->getResultArray();

        $pub = $db->table('publications')->where('submission_id', $id)->get()->getRowArray();

        $decision = $db->table('decisions')
            ->where('submission_id', $id)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $cert = null;

        // Publication cert (journal flow)
        if ($pub) {
            $cert = $db->table('certificate_issuances')
                ->where('type', 'publication')
                ->where('publication_id', (int)$pub['id'])
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()->getRowArray();
        }

        // Presentation cert (conference flow)
        $presentationCert = null;
        if ((string)($sub['type'] ?? '') === 'conference') {
            $presentationCert = $db->table('certificate_issuances')
                ->where('type', 'presentation')
                ->where('submission_id', $id)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()->getRowArray();
        }



        return view('admin/submissions/show', [
            'title' => 'Submission #' . $id,
            'sub' => $sub,
            'versions' => $versions,
            'publication' => $pub,
            'presentation_certificate' => $presentationCert,
            'decision' => $decision,
            'certificate' => $cert,
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }

    public function decide(int $id)
    {
        $db = \Config\Database::connect();

        $sub = $db->table('submissions')->where('id', $id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        if ($db->table('publications')->where('submission_id', $id)->countAllResults() > 0) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Already published. Decision locked.');
        }

        $decision = strtolower(trim((string)$this->request->getPost('decision')));
        $letter = trim((string)$this->request->getPost('letter_text'));

        $allowed = ['accept', 'reject', 'revise'];
        if (!in_array($decision, $allowed, true)) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Invalid decision.');
        }

        $editorId = (int)(session('auth_user')['id'] ?? 0);
        if ($editorId <= 0) {
            return redirect()->to('/login')->with('error', 'Session expired.');
        }

        $now = date('Y-m-d H:i:s');

        $db->transStart();

        $db->table('decisions')->insert([
            'submission_id' => $id,
            'editor_user_id' => $editorId,
            'decision' => $decision,
            'letter_text' => ($letter !== '' ? $letter : null),
            'created_at' => $now,
        ]);

        // map decision -> submission status (simple + forward-only)
        $newStatus = $decision === 'accept' ? 'accepted' : ($decision === 'reject' ? 'rejected' : 'revision_required');

        $db->table('submissions')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => $now,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Decision save failed.');
        }

        return redirect()->to('/admin/submissions/' . $id)->with('flash', 'Decision saved.');
    }

    public function publish(int $id)
    {
        $db = \Config\Database::connect();

        $sub = $db->table('submissions')->where('id', $id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        // Prevent double publish
        if ($db->table('publications')->where('submission_id', $id)->countAllResults() > 0) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Already published.');
        }

        // Gate: must be accepted (by status OR latest decision)
        $latestDecision = $db->table('decisions')
            ->where('submission_id', $id)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $isAccepted =
            ((string)($sub['status'] ?? '') === 'accepted') ||
            (!empty($latestDecision) && (string)($latestDecision['decision'] ?? '') === 'accept');

        if (!$isAccepted) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Cannot publish: submission not accepted.');
        }

        $volume = trim((string)$this->request->getPost('volume'));
        $issue  = trim((string)$this->request->getPost('issue'));
        $pages  = trim((string)$this->request->getPost('pages'));
        $doi    = trim((string)$this->request->getPost('doi'));

        if ($volume === '' || $issue === '' || $pages === '') {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Volume, Issue and Pages are required.');
        }

        $now = date('Y-m-d H:i:s');

        $db->transStart();

        // Create publication
        $db->table('publications')->insert([
            'submission_id' => $id,
            'published_at'  => $now,
            'volume'        => $volume,
            'issue'         => $issue,
            'pages'         => $pages,
            'doi'           => ($doi !== '' ? $doi : null),
            'citation_json' => null,
                    'published_file_path' => null,
        ]);

        $pubId = (int)$db->insertID();

        // Update submission
        $db->table('submissions')->where('id', $id)->update([
            'status'     => 'published',
            'updated_at' => $now,
        ]);

        // Issue publication certificate (REQUIRED)
        $code = 'AIRN-PUB-' . $id . '-' . substr(bin2hex(random_bytes(8)), 0, 8);

        $db->table('certificate_issuances')->insert([
            'user_id'        => (int)$sub['submitter_user_id'],
            'type'           => 'publication',
            'submission_id'  => $id,
            'publication_id' => $pubId,
            'code'           => $code,
            'issued_at'      => $now,
            'pdf_path'       => null,
            'meta_json'      => null,
        ]);

        $certId = (int)$db->insertID();

        // Fetch context (within the transaction so it’s consistent)
        $pub  = $db->table('publications')->where('id', $pubId)->get()->getRowArray();
        $user = $db->table('users')->where('id', (int)$sub['submitter_user_id'])->get()->getRowArray();

        if (!$pub || !$user) {
            $db->transComplete();
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Published but certificate context missing.');
        }

        
        // Auto-generate final published PDF (preferred) when manuscript is DOC/DOCX.
        // If manuscript is already PDF, AIRN cannot reliably re-typeset it without a PDF compositor.
        // In that case, admins may still upload a final PDF separately (future enhancement).
        $publishedFileRel = null;

        try {
            $subRow = $db->table('submissions')->where('id', $id)->get()->getRowArray();
            if (!empty($subRow['current_version_id'])) {
                $ver = $db->table('submission_versions')->where('id', (int)$subRow['current_version_id'])->get()->getRowArray();
                $manRel = (string)($ver['manuscript_path'] ?? '');
                if ($manRel !== '') {
                    $manAbs = WRITEPATH . 'uploads/' . ltrim($manRel, '/\\');
                    $ext = strtolower(pathinfo($manAbs, PATHINFO_EXTENSION));

                    if (in_array($ext, ['doc', 'docx'], true) && class_exists('PhpOffice\\PhpWord\\IOFactory')) {
                        $yearPub = (int)date('Y', strtotime((string)($pub['published_at'] ?? $now)));
                        $dirPub = WRITEPATH . 'uploads/published/' . $yearPub;
                        if (!is_dir($dirPub)) @mkdir($dirPub, 0775, true);

                        // Convert DOCX -> HTML
                        $phpWord = \PhpOffice\PhpWord\IOFactory::load($manAbs);
                        $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');

                        ob_start();
                        $htmlWriter->save('php://output');
                        $docHtml = (string)ob_get_clean();

                        // Basic cleanup (keep it conservative)
                        $docHtml = preg_replace('#<style[^>]*>.*?</style>#si', '', $docHtml);
                        $docHtml = preg_replace('#<meta[^>]*>#i', '', $docHtml);

                        // Prepare header strings (matches approved format structure)
                        $yearPub = (int)date('Y', strtotime((string)($pub['published_at'] ?? $now)));
                        $headerLeft = 'AIRN Journal of Computing Systems';
                        $headerRight = 'Vol. ' . (($pub['volume'] ?? '') !== '' ? $pub['volume'] : '—')
                            . ' • Issue ' . (($pub['issue'] ?? '') !== '' ? $pub['issue'] : '—')
                            . ' • ' . $yearPub;

                        // Use submission title; authors/affiliations are not stored structurally in this build,
                        // so we leave them blank unless you later add structured metadata.
                        $articleHtml = view('publications/article_pdf', [
                            'header_left'        => $headerLeft,
                            'header_right'       => $headerRight,
                            'doi'                => (string)($pub['doi'] ?? '-'),
                            'license'            => 'CC BY 4.0',
                            'received_at'        => !empty($sub['created_at']) ? date('d M Y', strtotime((string)$sub['created_at'])) : '-',
                            'accepted_at'        => !empty($latestDecision['created_at']) ? date('d M Y', strtotime((string)$latestDecision['created_at'])) : '-',
                            'published_at'       => date('d M Y', strtotime((string)($pub['published_at'] ?? $now))),
                            'article_id'         => 'AIRN-' . $yearPub . '-' . str_pad((string)$id, 3, '0', STR_PAD_LEFT),
                            'page_label'         => '',

                            'title'              => (string)($sub['title'] ?? ''),
                            'authors_html'       => '',
                            'affiliations_html'  => '',
                            'corresponding_html' => '',
                            'body_html'          => $docHtml,
                        ]);

                        $dom = new \Dompdf\Dompdf();
                        $opt = $dom->getOptions();
                        if (method_exists($opt, 'setIsRemoteEnabled')) {
                            $opt->setIsRemoteEnabled(true);
                        }
                        $dom->setOptions($opt);

                        $dom->loadHtml($articleHtml);
                        $dom->setPaper('A4', 'portrait');
                        $dom->render();

                        // Add page number in header right (similar placement to approved sample)
                        $canvas = $dom->getCanvas();
                        $font = $dom->getFontMetrics()->getFont('helvetica', 'normal');
                        // x, y tuned for A4 portrait with our header
                        $canvas->page_text(520, 32, "Page {PAGE_NUM}", $font, 9, [0,0,0]);

                        $publishedFileRel = 'uploads/published/' . $yearPub . '/published_' . $id . '_' . date('Ymd_His') . '.pdf';
                        $publishedAbs = WRITEPATH . $publishedFileRel;
                        file_put_contents($publishedAbs, $dom->output());
                    }
                }
            }
        } catch (\Throwable $e) {
            // Keep publish flow alive even if auto-typeset fails; admin can still proceed.
            $publishedFileRel = null;
        }

        // Persist published PDF path (if generated)
        if ($publishedFileRel) {
            $db->table('publications')->where('id', $pubId)->update([
                'published_file_path' => $publishedFileRel,
            ]);
        }

// Build certificate HTML
        $publishedAt = (string)($pub['published_at'] ?? $now);
        $year = (int)date('Y', strtotime($publishedAt));

        $brandRight = 'Vol. ' . (($pub['volume'] ?? '') !== '' ? $pub['volume'] : '—')
            . ' • Issue ' . (($pub['issue'] ?? '') !== '' ? $pub['issue'] : '—')
            . ' • ' . $year;

        $verifyUrl = base_url('verify/certificate/' . $code);

        $html = view('certificates/publication', [
            'brand_left'     => $this->brandLeft($sub),
            'brand_right'    => $brandRight,
            'recipient_name' => (string)($user['name'] ?? ''),
            'paper_title'    => (string)($sub['title'] ?? ''),
            'published_at'   => $publishedAt,
            'doi'            => $pub['doi'] ?? null,
            'volume'         => $pub['volume'] ?? null,
            'issue'          => $pub['issue'] ?? null,
            'pages'          => $pub['pages'] ?? null,
            'code'           => $code,
            'verify_url'     => $verifyUrl,
            'verify_short'   => 'airn/verify/' . $code, // short token to avoid PDF horizontal overflow
        ]);

        // Generate PDF immediately (no lazy generation)
        $dompdf = new \Dompdf\Dompdf();

        // NOTE:
        // - setIsHtml5ParserEnabled is deprecated in newer dompdf builds -> do NOT call it.
        // - Remote is only needed if you load external images/fonts via URL.
        $options = $dompdf->getOptions();
        if (method_exists($options, 'setIsRemoteEnabled')) {
            $options->setIsRemoteEnabled(true);
        }
        $dompdf->setOptions($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Save PDF
        $dir = WRITEPATH . 'certificates/' . $year;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $rel = 'certificates/' . $year . '/' . $code . '.pdf';
        $abs = WRITEPATH . $rel;

        file_put_contents($abs, $dompdf->output());

        $db->table('certificate_issuances')->where('id', $certId)->update([
            'pdf_path' => $rel,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Publish failed.');
        }

        return redirect()->to('/admin/submissions/' . $id)->with('flash', 'Published. Certificate issued.');
    }

    public function certificate(int $id)
    {
        $db = \Config\Database::connect();

        $sub = $db->table('submissions')->where('id', $id)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        $pub = $db->table('publications')->where('submission_id', $id)->get()->getRowArray();
        if (!$pub) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Not published yet.');
        }

        $cert = $db->table('certificate_issuances')
            ->where('type', 'publication')
            ->where('publication_id', (int)$pub['id'])
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$cert) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Certificate not found.');
        }

        // If already generated, download it.
        $pdfPathRaw = $cert['pdf_path'] ?? '';
        $pdfPath = is_string($pdfPathRaw) ? $pdfPathRaw : '';
        if ($pdfPath !== '') {
            $absExisting = WRITEPATH . ltrim($pdfPath, '/\\');
            if (is_file($absExisting)) {
                return $this->response->download($absExisting, null);
            }
        }

        // Regenerate (fallback)
        $user = $db->table('users')->where('id', (int)$cert['user_id'])->get()->getRowArray();
        if (!$user) {
            return redirect()->to('/admin/submissions/' . $id)->with('error', 'Certificate user missing.');
        }

        $publishedAt = (string)($pub['published_at'] ?? date('Y-m-d H:i:s'));
        $year = (int)date('Y', strtotime($publishedAt));
        $brandRight = 'Vol. ' . (($pub['volume'] ?? '') !== '' ? $pub['volume'] : '—')
            . ' • Issue ' . (($pub['issue'] ?? '') !== '' ? $pub['issue'] : '—')
            . ' • ' . $year;

        $certCode = (string)($cert['code'] ?? '');
        $verifyUrl = base_url('verify/certificate/' . $certCode);

        $html = view('certificates/publication', [
            'brand_left'     => $this->brandLeft($sub),
            'brand_right'    => $brandRight,
            'recipient_name' => (string)($user['name'] ?? ''),
            'paper_title'    => (string)($sub['title'] ?? ''),
            'published_at'   => $publishedAt,
            'doi'            => $pub['doi'] ?? null,
            'volume'         => $pub['volume'] ?? null,
            'issue'          => $pub['issue'] ?? null,
            'pages'          => $pub['pages'] ?? null,
            'code'           => $certCode,
            'verify_url'     => $verifyUrl,
            'verify_short'   => 'airn/verify/' . $certCode, // short token to avoid PDF horizontal overflow
        ]);

        $dompdf = new \Dompdf\Dompdf();

        // NOTE:
        // - setIsHtml5ParserEnabled is deprecated in newer dompdf builds.
        // - we do NOT call it.
        // - Remote is only needed if you load external images/fonts via URL.
        $options = $dompdf->getOptions();
        if (method_exists($options, 'setIsRemoteEnabled')) {
            $options->setIsRemoteEnabled(true);
        }
        $dompdf->setOptions($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dir = WRITEPATH . 'certificates/' . $year;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $rel = 'certificates/' . $year . '/' . $certCode . '.pdf';
        $abs = WRITEPATH . $rel;

        file_put_contents($abs, $dompdf->output());

        $db->table('certificate_issuances')
            ->where('id', (int)$cert['id'])
            ->update(['pdf_path' => $rel]);

        return $this->response->download($abs, null);
    }

    public function download(int $submissionId, int $versionId)
    {
        $db = \Config\Database::connect();

        $sub = $db->table('submissions')->where('id', $submissionId)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        $ver = $db->table('submission_versions')->where('id', $versionId)->get()->getRowArray();
        if (!$ver || (int)$ver['submission_id'] !== $submissionId) throw PageNotFoundException::forPageNotFound('Version not found');

        $mp = is_string($ver['manuscript_path'] ?? null) ? (string)$ver['manuscript_path'] : '';
        if ($mp === '') throw PageNotFoundException::forPageNotFound('No file');

        $path = WRITEPATH . 'uploads/' . ltrim($mp, '/\\');
        if (!is_file($path)) throw PageNotFoundException::forPageNotFound('File missing');

        return $this->response->download($path, null);
    }

    public function view(int $submissionId, int $versionId)
    {
        $db = \Config\Database::connect();

        $sub = $db->table('submissions')->where('id', $submissionId)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');

        $ver = $db->table('submission_versions')->where('id', $versionId)->get()->getRowArray();
        if (!$ver || (int)$ver['submission_id'] !== $submissionId) throw PageNotFoundException::forPageNotFound('Version not found');

        $mp = is_string($ver['manuscript_path'] ?? null) ? (string)$ver['manuscript_path'] : '';
        if ($mp === '') throw PageNotFoundException::forPageNotFound('No file');

        $path = WRITEPATH . 'uploads/' . ltrim($mp, '/\\');
        if (!is_file($path)) throw PageNotFoundException::forPageNotFound('File missing');

        $ext = strtolower((string)pathinfo($path, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            return redirect()->to('/admin/submissions/' . $submissionId)->with('error', 'Inline view is available for PDF only. Use download for DOC/DOCX.');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($path) . '"')
            ->setBody(file_get_contents($path));
    }

    private function brandLeft(array $sub): string
    {
        $db = \Config\Database::connect();

        $type = (string)($sub['type'] ?? '');
        if ($type === 'journal' && !empty($sub['journal_id'])) {
            $j = $db->table('journals')->where('id', (int)$sub['journal_id'])->get()->getRowArray();
            if ($j && is_string($j['name'] ?? null) && $j['name'] !== '') return (string)$j['name'];
        }

        if ($type === 'conference' && !empty($sub['conference_id'])) {
            $c = $db->table('conferences')->where('id', (int)$sub['conference_id'])->get()->getRowArray();
            if ($c && is_string($c['name'] ?? null) && $c['name'] !== '') return (string)$c['name'];
        }

        return 'AIRN';
    }
}
