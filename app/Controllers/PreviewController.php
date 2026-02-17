<?php

namespace App\Controllers;

use Dompdf\Dompdf;

class PreviewController extends BaseController
{
    public function certificate()
    {
        // HTML preview in browser
        return view('certificates/publication', [
            'brand_left' => 'AIRN Journal of Computing Systems',
            'brand_right' => 'Vol. 1 • Issue 1 • 2026',
            'recipient_name' => 'John Doe',
            'paper_title' => 'A Sample Paper Title for Certificate Preview',
            'published_at' => date('Y-m-d H:i:s'),
            'doi' => '10.1234/airn.2026.0001',
            'volume' => '1',
            'issue' => '1',
            'pages' => '1-10',
            'code' => 'AIRN-PUB-DEMO-ABC12345',
            'verify_url' => base_url('verify/certificate/AIRN-PUB-DEMO-ABC12345'),
        ]);
    }

    public function certificatePdf()
    {
        // Inline PDF preview (no DB)
        $html = view('certificates/publication', [
            'brand_left' => 'AIRN Journal of Computing Systems',
            'brand_right' => 'Vol. 1 • Issue 1 • 2026',
            'recipient_name' => 'John Doe',
            'paper_title' => 'A Sample Paper Title for Certificate Preview',
            'published_at' => date('Y-m-d H:i:s'),
            'doi' => '10.1234/airn.2026.0001',
            'volume' => '1',
            'issue' => '1',
            'pages' => '1-10',
            'code' => 'AIRN-PUB-DEMO-ABC12345',
            'verify_url' => base_url('verify/certificate/AIRN-PUB-DEMO-ABC12345'),
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Stream inline
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="certificate-preview.pdf"')
            ->setBody($dompdf->output());
    }
}
