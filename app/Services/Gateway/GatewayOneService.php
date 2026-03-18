<?php

namespace App\Services\Gateway;

use App\Services\Gateway\GatewayInterface;
use Illuminate\Support\Facades\Http;

class GatewayOneService implements GatewayInterface
{
    private string $baseUrl = 'http://localhost:3001';

    /*
     * AUTHENTICATION
     */
    private function getToken(): string
    {
        $response = Http::post("{$this->baseUrl}/login", [
            'email' => 'dev@betalent.tech',
            'token' => 'FEC9BB078BF338F464F96B48089EB498',
        ]);

        return $response->json('token');
    }
    /*
     * CREATE A NEW CLASS INSTANCE.
     */
    public function createTransaction(array $data): array
    {
        $token = $this->getToken();
        $makeTransaction = Http::withToken($token)
            ->post("{$this->baseUrl}/transactions", [
                'amount' => $data['amount'],
                'name' => $data['name'],
                'email' => $data['email'],
                'cardNumber' => $data['card_number'],
                'cvv' => $data['cvv'],
            ]);
        // logger($makeTransaction->json());
        $externalId = $makeTransaction->json('id');
        // logger('external_id: ' . $externalId);
        $getTransactions = Http::withToken($token)
            ->get("{$this->baseUrl}/transactions");
        // logger($getTransactions->json());
        // logger('transactions list: ', $getTransactions->json() ?? []);
        $detailedTransaction = collect($getTransactions->json('data'))
            ->firstWhere('id', $externalId);

        return [
            'id' => $externalId,
            'status' => $detailedTransaction['status'],
        ];
    }

    public function refund(string $externalId): array
    {
        $token = $this->getToken();
        $makeRefund = Http::withToken($token)
            ->post("{$this->baseUrl}/transactions/{$externalId}/charge_back");

        return $makeRefund->json();
    }
}
