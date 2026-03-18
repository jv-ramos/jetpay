<?php

namespace App\Services\Gateway;

use App\Services\Gateway\GatewayInterface;
use Illuminate\Support\Facades\Http;

class GatewayTwoService implements GatewayInterface
{
    private string $baseUrl = 'http://localhost:3002';

    private function authHeaders(): array
    {
        return [
            'Gateway-Auth-Token'  => 'tk_f2198cc671b5289fa856',
            'Gateway-Auth-Secret' => '3d15e8ed6131446ea7e3456728b1211f',
        ];
    }

    public function createTransaction(array $data): array
    {
        $response = Http::withHeaders($this->authHeaders())
            ->post("{$this->baseUrl}/transacoes", [
                'valor'       => $data['amount'],
                'nome'        => $data['name'],
                'email'       => $data['email'],
                'numeroCartao' => $data['card_number'],
                'cvv'         => $data['cvv'],
            ]);

        $externalId = $response->json('id');

        $transactions = Http::withHeaders($this->authHeaders())
            ->get("{$this->baseUrl}/transacoes");

        $detailedTransaction = collect($transactions->json('data'))
            ->firstWhere('id', $externalId);
        logger($detailedTransaction);
        return [
            'id'     => $externalId,
            'status' => $detailedTransaction['status'],
        ];
    }

    public function refund(string $externalId): array
    {
        $response = Http::withHeaders($this->authHeaders())
            ->post("{$this->baseUrl}/transacoes/reembolso", [
                'id' => $externalId,
            ]);

        $refundId = $response->json('id');

        $transactions = Http::withHeaders($this->authHeaders())
            ->get("{$this->baseUrl}/transacoes");

        $detailedTransaction = collect($transactions->json('data'))
            ->firstWhere('id', $refundId);

        return [
            'id'     => $refundId,
            'status' => $detailedTransaction['status'],
        ];
    }
}
