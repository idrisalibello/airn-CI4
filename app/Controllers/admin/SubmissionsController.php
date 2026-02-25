<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use setasign\Fpdi\Fpdi;

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

        // if ((string)($sub['type'] ?? '') !== 'journal') {
        //     return redirect()->to('/admin/submissions/' . $id)->with('error', 'Publish is for journal submissions only.');
        // }

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

        $confSettings = [];
        if (!empty($conf['settings_json'])) {
            $tmp = json_decode((string)$conf['settings_json'], true);
            if (is_array($tmp)) $confSettings = $tmp;
        }
        $confTheme = trim((string)($confSettings['theme'] ?? ''));

        $year = (int)date('Y', strtotime((string)($conf['start_date'] ?? $now)));

        $brandRight = 'Conference • ' . $year;

        $verifyUrl = base_url('verify/' . $code);

        $html = view('certificates/presentation', [
            'brand_left'       => $this->brandLeft($sub),
            'brand_right'      => $brandRight,
            'recipient_name'   => (string)($user['name'] ?? ''),
            'paper_title'      => (string)($sub['title'] ?? ''),
            'authors' => ($sub['authors'] ?? ($user['name'] ?? '')),
            'conference_name'  => (string)($conf['name'] ?? ''),
            'conference_theme' => $confTheme,
            'conference_venue' => (string)($conf['venue'] ?? ''),
            'start_date'       => (string)($conf['start_date'] ?? ''),
            'end_date'         => (string)($conf['end_date'] ?? ''),
            'code'             => $code,
            'verify_url'       => $verifyUrl,
            'verify_short'     => base_url('verify/' . $code),
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

        $verifyUrl = base_url('verify/' . $certCode);

        $html = view('certificates/presentation', [
            'brand_left'       => $this->brandLeft($sub),
            'brand_right'      => $brandRight,
            'recipient_name'   => (string)($user['name'] ?? ''),
            'paper_title'      => (string)($sub['title'] ?? ''),
            'authors' => ($sub['authors'] ?? ($user['name'] ?? '')),
            'conference_name'  => (string)($conf['name'] ?? ''),
            'conference_venue' => (string)($conf['venue'] ?? ''),
            'start_date'       => (string)($conf['start_date'] ?? ''),
            'end_date'         => (string)($conf['end_date'] ?? ''),
            'code'             => $certCode,
            'verify_url'       => $verifyUrl,
            'verify_short'     => base_url('verify/' . $certCode),
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
        if (!$sub) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Submission not found');
        }

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

        /**
         * CAMERA-READY POLICY (HARD GATE):
         * - Publishing requires an uploaded camera-ready PDF (final author PDF).
         * - Only PDF is accepted.
         * - Stamping is applied to the camera-ready PDF to generate the final published copy.
         */
        $file = $this->request->getFile('camera_ready_pdf');
        if (!$file || !$file->isValid()) {
            return redirect()->to('/admin/submissions/' . $id)
                ->with('error', 'Camera-ready PDF is required for publishing.');
        }

        $extUp = strtolower((string)$file->getClientExtension());
        if ($extUp !== 'pdf') {
            return redirect()->to('/admin/submissions/' . $id)
                ->with('error', 'Camera-ready file must be a PDF.');
        }

        // Optional but sensible: reject empty/too-small uploads
        if (method_exists($file, 'getSize') && (int)$file->getSize() < 1024) {
            return redirect()->to('/admin/submissions/' . $id)
                ->with('error', 'Camera-ready PDF looks invalid (file too small). Please upload the final camera-ready PDF.');
        }

        // Stamping library must exist
        if (!class_exists(\setasign\Fpdi\Fpdi::class)) {
            return redirect()->to('/admin/submissions/' . $id)
                ->with('error', 'PDF stamping library missing. Run: composer require setasign/fpdi-fpdf');
        }

        $now = date('Y-m-d H:i:s');

        // ---- Transaction starts ONLY after camera-ready is validated ----
        $db->transBegin();

        $cameraAbs = '';
        $publishedAbs = '';
        $cameraRel = '';
        $publishedFileRel = '';

        try {
            // Create publication
            $db->table('publications')->insert([
                'submission_id'       => $id,
                'published_at'        => $now,
                'volume'              => $volume,
                'issue'               => $issue,
                'pages'               => $pages,
                'doi'                 => ($doi !== '' ? $doi : null),
                'citation_json'       => null,
                'published_file_path' => null,
            ]);

            $pubId = (int)$db->insertID();
            if ($pubId <= 0) {
                throw new \RuntimeException('Failed to create publication record.');
            }

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
            if ($certId <= 0) {
                throw new \RuntimeException('Failed to create certificate issuance.');
            }

            // Fetch context (within the transaction so it’s consistent)
            $pub  = $db->table('publications')->where('id', $pubId)->get()->getRowArray();
            $user = $db->table('users')->where('id', (int)$sub['submitter_user_id'])->get()->getRowArray();

            if (!$pub || !$user) {
                throw new \RuntimeException('Certificate context missing.');
            }

            // FINAL PUBLISHED PDF (camera-ready + system stamping)
            $yearPub = (int)date('Y', strtotime((string)($pub['published_at'] ?? $now)));

            $dirCamera = WRITEPATH . 'uploads/camera_ready/' . $yearPub;
            if (!is_dir($dirCamera)) {
                @mkdir($dirCamera, 0775, true);
            }

            $dirPublished = WRITEPATH . 'uploads/published/' . $yearPub;
            if (!is_dir($dirPublished)) {
                @mkdir($dirPublished, 0775, true);
            }

            $cameraRel = 'uploads/camera_ready/' . $yearPub . '/camera_ready_' . $id . '_' . date('Ymd_His') . '.pdf';
            $cameraAbs = WRITEPATH . $cameraRel;

            $publishedFileRel = 'uploads/published/' . $yearPub . '/published_' . $id . '_' . date('Ymd_His') . '.pdf';
            $publishedAbs = WRITEPATH . $publishedFileRel;

            // Move uploaded camera-ready into storage
            $file->move(dirname($cameraAbs), basename($cameraAbs), true);

            $journalName = $this->brandLeft($sub);
            $vol = (string)($pub['volume'] ?? '—');
            $iss = (string)($pub['issue'] ?? '—');

            $doiRaw  = (string)($pub['doi'] ?? '');
            $doiText = $doiRaw !== '' ? $doiRaw : '—';

            $pubUrl = base_url('published/' . (int)$pubId);

            // Header/footers must match the AIRN sample article style
            $headerRow1Right  = 'Vol. ' . ($vol !== '' ? $vol : '—') . ' • Issue ' . ($iss !== '' ? $iss : '—') . ' • ' . $yearPub;
            $headerRow1Center = $journalName . ' ' . $headerRow1Right;
            $headerRow2Left   = 'DOI: ' . $doiText . ' • Licensed under CC BY 4.0';

            $receivedAt  = (string)($sub['created_at'] ?? '');
            $acceptedAt  = (string)($sub['decided_at'] ?? ($sub['accepted_at'] ?? ''));
            $publishedAt = (string)($pub['published_at'] ?? $now);

            $this->stampJournalPdf($cameraAbs, $publishedAbs, [
                'header_row1_center' => $headerRow1Center,
                'header_row2_left'   => $headerRow2Left,
                'footer_left'        => $pubUrl,
                'page_prefix'        => 'Page ',
                'doi'                => $doiText,
                'article_id'         => (string)($pub['article_id'] ?? $pubId),
                'received_at'        => $receivedAt !== '' ? date('Y-m-d', strtotime($receivedAt)) : '—',
                'accepted_at'        => $acceptedAt !== '' ? date('Y-m-d', strtotime($acceptedAt)) : '—',
                'published_at'       => $publishedAt !== '' ? date('Y-m-d', strtotime($publishedAt)) : '—',
            ]);

            if (!is_file($publishedAbs) || filesize($publishedAbs) < 1024) {
                throw new \RuntimeException('Stamped PDF not created.');
            }

            // Persist published path
            $db->table('publications')->where('id', $pubId)->update([
                'published_file_path' => $publishedFileRel,
            ]);

            // Build certificate HTML
            $publishedAt = (string)($pub['published_at'] ?? $now);
            $year = (int)date('Y', strtotime($publishedAt));

            $brandRight = 'Vol. ' . (($pub['volume'] ?? '') !== '' ? $pub['volume'] : '—')
                . ' • Issue ' . (($pub['issue'] ?? '') !== '' ? $pub['issue'] : '—')
                . ' • ' . $year;

            $verifyUrl = base_url('verify/' . $code);

            $html = view('certificates/publication', [
                'brand_left'      => $this->brandLeft($sub),
                'brand_right'     => $brandRight,
                'recipient_name'  => (string)($user['name'] ?? ''),
                'paper_title'     => (string)($sub['title'] ?? ''),
                'authors'         => (string)((($sub['authors'] ?? '') !== '') ? $sub['authors'] : ($user['name'] ?? '')),
                'published_at'    => $publishedAt,
                'doi'             => $pub['doi'] ?? null,
                'volume'          => $pub['volume'] ?? null,
                'issue'           => $pub['issue'] ?? null,
                'pages'           => $pub['pages'] ?? null,
                'code'            => $code,
                'verify_url'      => $verifyUrl,
                'verify_short'    => base_url('verify/' . $code),
            ]);

            // Generate PDF immediately (no lazy generation)
            $dompdf = new \Dompdf\Dompdf();

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

            // Commit
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed.');
            }
            $db->transCommit();

            return redirect()->to('/admin/submissions/' . $id)->with('flash', 'Published. Certificate issued.');
        } catch (\Throwable $e) {
            // Cleanup files if created
            if ($cameraAbs && is_file($cameraAbs)) {
                @unlink($cameraAbs);
            }
            if ($publishedAbs && is_file($publishedAbs)) {
                @unlink($publishedAbs);
            }

            // Rollback DB state so submission/publication never becomes "published" on failure
            if ($db->transStatus() !== false) {
                $db->transRollback();
            } else {
                $db->transRollback();
            }

            return redirect()->to('/admin/submissions/' . $id)
                ->with('error', 'Publish failed: ' . $e->getMessage());
        }
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
        $verifyUrl = base_url('verify/' . $certCode);

        $html = view('certificates/publication', [
            'brand_left'     => $this->brandLeft($sub),
            'brand_right'    => $brandRight,
            'recipient_name' => (string)($user['name'] ?? ''),
            'paper_title'    => (string)($sub['title'] ?? ''),
            'authors'       => (string)((($sub['authors'] ?? '') !== '') ? $sub['authors'] : ($user['name'] ?? '')),
            'published_at'   => $publishedAt,
            'doi'            => $pub['doi'] ?? null,
            'volume'         => $pub['volume'] ?? null,
            'issue'          => $pub['issue'] ?? null,
            'pages'          => $pub['pages'] ?? null,
            'code'           => $certCode,
            'verify_url'     => $verifyUrl,
            'verify_short'     => base_url('verify/' . $certCode),
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

    private function stampJournalPdf(string $srcAbs, string $dstAbs, array $meta): void
    {
        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($srcAbs);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size  = $pdf->getTemplateSize($tplId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            $left  = 10;
            $right = 10;

            // Header/footer band positions (in mm units used by FPDI/FPDF)
            $topYRow1 = 6;
            $topYRow2 = 10.5;
            $headerLineY = 15;

            $botY  = $size['height'] - 8;
            $footerLineY = $size['height'] - 10.5;

            $h1C = (string)($meta['header_row1_center'] ?? '');
            $h1L = (string)($meta['header_row1_left'] ?? ($meta['header_left'] ?? ''));
            $h1R = (string)($meta['header_row1_right'] ?? ($meta['header_right'] ?? ''));
            $h2L = (string)($meta['header_row2_left'] ?? '');
            $footerLeft  = (string)($meta['footer_left'] ?? '');
            $pagePrefix  = (string)($meta['page_prefix'] ?? 'Page ');

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Times', '', 9);

            // Header row 1
            if ($h1C !== '') {
                $pdf->SetXY($left, $topYRow1);
                $pdf->Cell($size['width'] - $left - $right, 4, $h1C, 0, 0, 'C');
            } elseif ($h1L !== '' || $h1R !== '') {
                $pdf->SetXY($left, $topYRow1);
                $pdf->Cell($size['width'] - $left - $right, 4, $h1L, 0, 0, 'L');

                $pdf->SetXY($left, $topYRow1);
                $pdf->Cell($size['width'] - $left - $right, 4, $h1R, 0, 0, 'R');
            }

            // Header row 2 (left) and Page label (right)
            $pageTxt = $pagePrefix . $pageNo . ' of ' . $pageCount;

            $pdf->SetXY($left, $topYRow2);
            $pdf->Cell($size['width'] - $left - $right, 4, $h2L, 0, 0, 'L');

            $pdf->SetXY($left, $topYRow2);
            $pdf->Cell($size['width'] - $left - $right, 4, $pageTxt, 0, 0, 'R');

            // Thin separator under header
            $pdf->Line($left, $headerLineY, $size['width'] - $right, $headerLineY);

            // Meta lines on page 1 (matches sample article structure)
            if ($pageNo === 1) {
                $receivedAt  = (string)($meta['received_at'] ?? '—');
                $acceptedAt  = (string)($meta['accepted_at'] ?? '—');
                $publishedAt = (string)($meta['published_at'] ?? '—');
                $articleId   = (string)($meta['article_id'] ?? '—');
                $doi         = (string)($meta['doi'] ?? '—');

                $pdf->SetFont('Times', '', 9);
                $metaY1 = 18;
                $metaY2 = 22;

                $line1 = 'Received: ' . $receivedAt . '   Accepted: ' . $acceptedAt . '   Published: ' . $publishedAt;
                $line2 = 'Article ID: ' . $articleId . '   DOI: ' . $doi;

                $pdf->SetXY($left, $metaY1);
                $pdf->MultiCell($size['width'] - $left - $right, 4, $line1, 0, 'L');

                $pdf->SetXY($left, $metaY2);
                $pdf->MultiCell($size['width'] - $left - $right, 4, $line2, 0, 'L');
            }

            // Footer
            if (strlen($footerLeft) > 90) {
                $footerLeft = substr($footerLeft, 0, 87) . '...';
            }

            // Thin separator above footer
            $pdf->Line($left, $footerLineY, $size['width'] - $right, $footerLineY);

            $pdf->SetFont('Times', '', 9);
            $pdf->SetXY($left, $botY);
            $pdf->Cell($size['width'] - $left - $right, 4, $footerLeft, 0, 0, 'L');
        }

        $pdf->Output($dstAbs, 'F');
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

//test
