<?php

namespace App\Services\Gateway;

use App\Models\Transaction;
use App\Services\Gateway\GatewayInterface;

class GatewayTwoService implements GatewayInterface
{
    public function __construct(
        private GatewayRequestService $requestService
    ) {}

    private function authHeaders(): array
    {
        return [
            'Gateway-Auth-Token'  => 'tk_f2198cc671b5289fa856',
            'Gateway-Auth-Secret' => '3d15e8ed6131446ea7e3456728b1211f',
        ];
    }

    public function createTransaction(array $data): array
    {
        $headers = $this->authHeaders();
        $transactionData = [
            'valor' => $data['amount'],
            'nome' => $data['name'],
            'email' => $data['email'],
            'numeroCartao' => $data['card_number'],
            'cvv' => $data['cvv'],
        ];

        $response = $this->requestService
            ->withHeaders($headers)
            ->post()
            ->send('/transacoes', $transactionData);

        logger($response);
        $externalId = $response['id'];

        $transactions = $this->requestService
            ->withHeaders($headers)
            ->get()
            ->send('/transacoes');

        $detailedTransaction = collect($transactions['data'])
            ->firstWhere('id', $externalId);

        return [
            'id'     => $externalId,
            'status' => $detailedTransaction['status'],
        ];
    }

    public function refund(Transaction $transaction): array
    {
        $headers = $this->authHeaders();
        $externalId = $transaction->external_id;

        $this->requestService
            ->withHeaders($headers)
            ->post()
            ->send("/transacoes/reembolso", ["id" => $externalId]);

        $transactions = $this->requestService
            ->withHeaders($headers)
            ->get()
            ->send('/transacoes');

        $detailedTransaction = collect($transactions['data'])
            ->firstWhere('id', $externalId);

        return [
            'id'     => $externalId,
            'status' => $detailedTransaction['status'],
        ];
    }
}
