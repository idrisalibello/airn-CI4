<?php

namespace App\Controllers\Author;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;

class CertificatesController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $uid = (int)(session('auth_user')['id'] ?? 0);

        $items = $db->table('certificate_issuances')
            ->where('user_id', $uid)
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        return view('author/certificates/index', [
            'title' => 'My Certificates',
            'items' => $items,
            'flash' => session('flash'),
            'error' => session('error'),
        ]);
    }

    public function download(string $code)
    {
        $db = \Config\Database::connect();
        $uid = (int)(session('auth_user')['id'] ?? 0);

        $cert = $db->table('certificate_issuances')->where('code', $code)->get()->getRowArray();
        if (!$cert) throw PageNotFoundException::forPageNotFound('Certificate not found');
        if ((int)$cert['user_id'] !== $uid) return redirect()->to('/author/certificates')->with('error', 'Access denied.');

        if (!empty($cert['pdf_path'])) {
            $abs = WRITEPATH . ltrim($cert['pdf_path'], '/\\');
            if (is_file($abs)) return $this->response->download($abs, null);
        }

        $type = (string)($cert['type'] ?? '');
        if (!in_array($type, ['publication', 'presentation'], true)) {
            return redirect()->to('/author/certificates')->with('error', 'Unsupported certificate type.');
        }

        $sub  = $db->table('submissions')->where('id', (int)$cert['submission_id'])->get()->getRowArray();
        $user = $db->table('users')->where('id', $uid)->get()->getRowArray();

        if (!$sub || !$user) {
            return redirect()->to('/author/certificates')->with('error', 'Certificate context missing.');
        }

        // =========================
        // PRESENTATION CERTIFICATE
        // =========================
        if ($type === 'presentation') {

            if ((string)($sub['type'] ?? '') !== 'conference' || empty($sub['conference_id'])) {
                return redirect()->to('/author/certificates')->with('error', 'Conference context missing.');
            }

            $conf = $db->table('conferences')->where('id', (int)$sub['conference_id'])->get()->getRowArray();
            if (!$conf) {
                return redirect()->to('/author/certificates')->with('error', 'Conference not found.');
            }

            $year = (int)date('Y', strtotime((string)($conf['start_date'] ?? date('Y-m-d'))));
            $brandRight = 'Conference • ' . $year;

            $html = view('certificates/presentation', [
                'brand_left'       => 'AIRN Conference',
                'brand_right'      => $brandRight,
                'recipient_name'   => (string)($user['name'] ?? ''),
                'paper_title'      => (string)($sub['title'] ?? ''),
                'conference_name'  => (string)($conf['name'] ?? ''),
                'conference_venue' => (string)($conf['venue'] ?? ''),
                'start_date'       => (string)($conf['start_date'] ?? ''),
                'end_date'         => (string)($conf['end_date'] ?? ''),
                'code'             => (string)($cert['code'] ?? ''),
                'verify_url'       => base_url('verify/certificate/' . (string)($cert['code'] ?? '')),
                'verify_short'     => 'airn/verify/' . (string)($cert['code'] ?? ''),
            ]);

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $dir = WRITEPATH . 'certificates/' . $year;
            if (!is_dir($dir)) @mkdir($dir, 0775, true);

            $rel = 'certificates/' . $year . '/' . $cert['code'] . '.pdf';
            $abs = WRITEPATH . $rel;
            file_put_contents($abs, $dompdf->output());

            $db->table('certificate_issuances')->where('id', (int)$cert['id'])->update(['pdf_path' => $rel]);

            return $this->response->download($abs, null);
        }

        // =========================
        // PUBLICATION CERTIFICATE (existing logic)
        // =========================
        $pub = $db->table('publications')->where('id', (int)$cert['publication_id'])->get()->getRowArray();
        if (!$pub) {
            return redirect()->to('/author/certificates')->with('error', 'Publication context missing.');
        }

        $year = (int)date('Y', strtotime($pub['published_at'] ?? date('Y-m-d')));
        $brandRight = 'Vol. ' . ($pub['volume'] ?: '—') . ' • Issue ' . ($pub['issue'] ?: '—') . ' • ' . $year;

        $html = view('certificates/publication', [
            'brand_left'     => 'AIRN Journal of Computing Systems',
            'brand_right'    => $brandRight,
            'recipient_name' => $user['name'],
            'paper_title'    => $sub['title'],
            'published_at'   => $pub['published_at'],
            'doi'            => $pub['doi'],
            'volume'         => $pub['volume'],
            'issue'          => $pub['issue'],
            'pages'          => $pub['pages'],
            'code'           => $cert['code'],
            'verify_url'     => base_url('verify/certificate/' . $cert['code']),
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dir = WRITEPATH . 'certificates/' . $year;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        $rel = 'certificates/' . $year . '/' . $cert['code'] . '.pdf';
        $abs = WRITEPATH . $rel;
        file_put_contents($abs, $dompdf->output());

        $db->table('certificate_issuances')->where('id', (int)$cert['id'])->update(['pdf_path' => $rel]);

        return $this->response->download($abs, null);
    }
}
