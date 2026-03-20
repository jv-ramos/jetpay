<?php

namespace App\Services\Gateway;

use App\Models\Transaction;
use App\Services\Gateway\GatewayInterface;

class GatewayOneService implements GatewayInterface
{
    public function __construct(
        private GatewayRequestService $requestService
    ) {}
    /*
     * AUTHENTICATION
     */
    private function getToken(): string
    {
        $credentials = [
            'email' => 'dev@betalent.tech',
            'token' => 'FEC9BB078BF338F464F96B48089EB498',
        ];

        $response = $this->requestService
            ->post()
            ->send('/login', $credentials);

        return $response['token'];
    }
    /*
     * CREATE A NEW CLASS INSTANCE.
     */
    public function createTransaction(array $data): array
    {
        $token = $this->getToken();
        $transactionData = [
            'amount' => $data['amount'],
            'name' => $data['name'],
            'email' => $data['email'],
            'cardNumber' => $data['card_number'],
            'cvv' => $data['cvv'],
        ];

        $makeTransaction = $this->requestService
            ->withToken($token)
            ->send('/transactions', $transactionData);

        $externalId = $makeTransaction['id'];

        $getTransactions = $this->requestService
            ->withToken($token)
            ->get()
            ->send('/transactions');

        $detailedTransaction = collect($getTransactions['data'])
            ->firstWhere('id', $externalId);

        return [
            'id' => $externalId,
            'status' => $detailedTransaction['status'],
        ];
    }

    public function refund(Transaction $transaction): array
    {
        $token = $this->getToken();
        $makeRefund = $this->requestService
            ->withToken($token)
            ->post()
            ->send("/transactions/{$transaction->external_id}/charge_back");

        return $makeRefund;
    }
}
