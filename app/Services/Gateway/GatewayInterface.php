<?php

namespace App\Services\Gateway;

interface GatewayInterface
{
    public function createTransaction(array $data): array;
    public function refund(string $externalId): array;
}
