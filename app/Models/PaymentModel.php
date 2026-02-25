<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    public    $useTimestamps    = false;

    protected $allowedFields = [
        'submission_id',
        'author_id',
        'purpose',
        'amount_kobo',
        'currency',
        'reference',
        'authorization_url',
        'access_code',
        'status',
        'paystack_transaction_id',
        'paid_at',
        'channel',
        'gateway_response',
        'raw_verify_json',
        'raw_webhook_json',
        'created_at',
        'updated_at',
    ];
}