<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class PaymentsController extends BaseController
{
    private function paystackSecret(): string
    {
        $k = (string)getenv('PAYSTACK_SECRET_KEY');
        return trim($k);
    }

    private function feeKobo(string $purpose): int
    {
        // Configure these in .env
        // JOURNAL_FEE_KOBO=2000000   (₦20,000)
        // CONFERENCE_FEE_KOBO=1500000 (₦15,000)
        if ($purpose === 'journal') {
            return (int)getenv('JOURNAL_FEE_KOBO');
        }
        if ($purpose === 'conference') {
            return (int)getenv('CONFERENCE_FEE_KOBO');
        }
        return 0;
    }

    private function httpJson(string $method, string $url, array $headers, ?array $payload = null): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $h = [];
        foreach ($headers as $k => $v) $h[] = $k . ': ' . $v;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $h);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload ?? []));
        }

        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            return ['ok' => false, 'code' => $code, 'error' => $err ?: 'cURL error', 'raw' => null];
        }

        $json = json_decode($raw, true);
        return ['ok' => ($code >= 200 && $code < 300), 'code' => $code, 'error' => null, 'raw' => $raw, 'json' => $json];
    }

    private function verifyReference(string $reference): array
    {
        $sk = $this->paystackSecret();
        if ($sk === '') return ['ok' => false, 'error' => 'Paystack secret key not configured'];

        $url = 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference);

        $res = $this->httpJson('GET', $url, [
            'Authorization' => 'Bearer ' . $sk,
            'Content-Type'  => 'application/json',
        ]);

        if (!$res['ok']) {
            return ['ok' => false, 'error' => 'Paystack verify failed', 'res' => $res];
        }

        $data = $res['json']['data'] ?? null;
        if (!is_array($data)) {
            return ['ok' => false, 'error' => 'Invalid verify response', 'res' => $res];
        }

        return ['ok' => true, 'data' => $data, 'raw' => (string)($res['raw'] ?? '')];
    }

    public function initialize(int $submissionId)
    {
        $db = \Config\Database::connect();
        $auth = session('auth_user');
        $uid = (int)($auth['id'] ?? 0);
        if ($uid <= 0) return redirect()->to('/login')->with('error', 'Session expired.');

        $sub = $db->table('submissions')->where('id', $submissionId)->get()->getRowArray();
        if (!$sub) throw PageNotFoundException::forPageNotFound('Submission not found');
        if ((int)$sub['submitter_user_id'] !== $uid) {
            return redirect()->to('/author/submissions')->with('error', 'Access denied.');
        }

        $purpose = (string)($sub['type'] ?? '');
        if (!in_array($purpose, ['journal', 'conference'], true)) {
            return redirect()->to('/author/submissions/' . $submissionId)->with('error', 'Invalid submission type.');
        }

        $fee = $this->feeKobo($purpose);
        if ($fee <= 0) {
            return redirect()->to('/author/submissions/' . $submissionId)->with('error', 'Payment fee not configured.');
        }

        // If already PAID, block duplicate payment
        $pm = new PaymentModel();
        $paid = $pm->where('submission_id', $submissionId)->where('status', 'PAID')->first();
        if ($paid) {
            return redirect()->to('/author/submissions/' . $submissionId)->with('flash', 'Payment already confirmed.');
        }

        $user = $db->table('users')->where('id', $uid)->get()->getRowArray();
        $email = (string)($user['email'] ?? '');
        if ($email === '') {
            return redirect()->to('/author/submissions/' . $submissionId)->with('error', 'Missing user email.');
        }

        $sk = $this->paystackSecret();
        if ($sk === '') {
            return redirect()->to('/author/submissions/' . $submissionId)->with('error', 'Paystack secret key not configured.');
        }

        $reference = 'AIRN-' . strtoupper($purpose) . '-' . $submissionId . '-' . substr(bin2hex(random_bytes(10)), 0, 12);
        $now = date('Y-m-d H:i:s');

        // Create local payment attempt
        $pm->insert([
            'submission_id' => $submissionId,
            'author_id'     => $uid,
            'purpose'       => $purpose,
            'amount_kobo'   => $fee,
            'currency'      => 'NGN',
            'reference'     => $reference,
            'authorization_url' => null,
            'access_code'        => null,
            'status'       => 'PENDING',
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        $callback = base_url('payments/callback');

        $init = $this->httpJson('POST', 'https://api.paystack.co/transaction/initialize', [
            'Authorization' => 'Bearer ' . $sk,
            'Content-Type'  => 'application/json',
        ], [
            'email'        => $email,
            'amount'       => $fee,
            'currency'     => 'NGN',
            'reference'    => $reference,
            'callback_url' => $callback,
            'metadata'     => [
                'submission_id' => $submissionId,
                'author_id'     => $uid,
                'purpose'       => $purpose,
            ],
        ]);

        if (!$init['ok'] || empty($init['json']['status'])) {
            // mark failed
            $pm->where('reference', $reference)->set([
                'status' => 'FAILED',
                'updated_at' => date('Y-m-d H:i:s'),
            ])->update();

            return redirect()->to('/author/submissions/' . $submissionId)->with('error', 'Payment initialization failed.');
        }

        $authUrl = (string)($init['json']['data']['authorization_url'] ?? '');
        $access  = (string)($init['json']['data']['access_code'] ?? '');

        $pm->where('reference', $reference)->set([
            'authorization_url' => ($authUrl !== '' ? $authUrl : null),
            'access_code'       => ($access !== '' ? $access : null),
            'updated_at'        => date('Y-m-d H:i:s'),
        ])->update();

        if ($authUrl === '') {
            return redirect()->to('/author/submissions/' . $submissionId)->with('error', 'Payment link not returned.');
        }

        return redirect()->to($authUrl);
    }

    public function callback()
    {
        $reference = trim((string)$this->request->getGet('reference'));
        if ($reference === '') {
            return redirect()->to('/author/submissions')->with('error', 'Missing payment reference.');
        }

        $pm = new PaymentModel();
        $pay = $pm->where('reference', $reference)->first();
        if (!$pay) {
            return redirect()->to('/author/submissions')->with('error', 'Payment record not found.');
        }

        // Verify with Paystack (source of truth)
        $ver = $this->verifyReference($reference);
        if (!$ver['ok']) {
            $pm->where('reference', $reference)->set([
                'status' => 'FAILED',
                'updated_at' => date('Y-m-d H:i:s'),
            ])->update();

            return redirect()->to('/author/submissions/' . (int)$pay['submission_id'])->with('error', 'Payment verification failed.');
        }

        $data = $ver['data'];
        $success = ((string)($data['status'] ?? '') === 'success');

        $update = [
            'raw_verify_json' => $ver['raw'] ?? null,
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($success) {
            $update['status'] = 'PAID';
            $update['paystack_transaction_id'] = isset($data['id']) ? (int)$data['id'] : null;
            $update['paid_at'] = !empty($data['paid_at']) ? date('Y-m-d H:i:s', strtotime((string)$data['paid_at'])) : date('Y-m-d H:i:s');
            $update['channel'] = (string)($data['channel'] ?? null);
            $update['gateway_response'] = (string)($data['gateway_response'] ?? null);
        } else {
            $update['status'] = 'FAILED';
        }

        $pm->where('reference', $reference)->set($update)->update();

        $sid = (int)($pay['submission_id'] ?? 0);

        if ($success) {
            return redirect()->to('/author/submissions/' . $sid)->with('flash', 'Payment confirmed. Your submission is now eligible for review.');
        }
        return redirect()->to('/author/submissions/' . $sid)->with('error', 'Payment not successful.');
    }

    public function webhook()
    {
        $sk = $this->paystackSecret();
        if ($sk === '') {
            return $this->response->setStatusCode(500)->setBody('secret-not-configured');
        }

        $raw = (string)$this->request->getBody();

        $sig = (string)$this->request->getHeaderLine('x-paystack-signature');
        $calc = hash_hmac('sha512', $raw, $sk);

        if ($sig === '' || !hash_equals($calc, $sig)) {
            return $this->response->setStatusCode(401)->setBody('invalid-signature');
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return $this->response->setStatusCode(400)->setBody('invalid-json');
        }

        $event = (string)($payload['event'] ?? '');
        $data  = $payload['data'] ?? null;

        if (!is_array($data)) {
            return $this->response->setStatusCode(200)->setBody('ok');
        }

        $reference = (string)($data['reference'] ?? '');
        if ($reference === '') {
            return $this->response->setStatusCode(200)->setBody('ok');
        }

        $pm = new PaymentModel();
        $pay = $pm->where('reference', $reference)->first();
        if (!$pay) {
            return $this->response->setStatusCode(200)->setBody('ok');
        }

        // Always store raw webhook (auditable)
        $pm->where('reference', $reference)->set([
            'raw_webhook_json' => $raw,
            'updated_at'       => date('Y-m-d H:i:s'),
        ])->update();

        if ($event !== 'charge.success') {
            return $this->response->setStatusCode(200)->setBody('ok');
        }

        // Verify again to confirm (never trust webhook alone)
        $ver = $this->verifyReference($reference);
        if ($ver['ok']) {
            $v = $ver['data'];
            if ((string)($v['status'] ?? '') === 'success') {
                $pm->where('reference', $reference)->set([
                    'status' => 'PAID',
                    'paystack_transaction_id' => isset($v['id']) ? (int)$v['id'] : null,
                    'paid_at' => !empty($v['paid_at']) ? date('Y-m-d H:i:s', strtotime((string)$v['paid_at'])) : date('Y-m-d H:i:s'),
                    'channel' => (string)($v['channel'] ?? null),
                    'gateway_response' => (string)($v['gateway_response'] ?? null),
                    'raw_verify_json' => $ver['raw'] ?? null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ])->update();
            }
        }

        return $this->response->setStatusCode(200)->setBody('ok');
    }
}