<?php

namespace App\Services\Gateway;

use App\Models\Transaction;

interface GatewayInterface
{
    //TODO: Abstraction by Heritance
    public function createTransaction(array $data): array;
    public function refund(Transaction $transaction): array;
}
