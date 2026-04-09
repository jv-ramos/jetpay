<?php

namespace App\Services\Gateway;

use App\Models\Transaction;

interface GatewayInterface
{
    public function createTransaction(array $data): array;
    public function refund(Transaction $transaction): array;
}
