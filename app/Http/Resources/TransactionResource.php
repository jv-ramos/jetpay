<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'client_id'              => $this->client_id,
            'gateway_id'             => $this->gateway_id,
            'external_id'            => $this->external_id,
            'status'                 => $this->status,
            'amount'                 => $this->amount,
            'card_last_numbers'      => $this->card_last_numbers,
            'created_at'             => $this->created_at,
            'products'               => ProductTransactionResource::collection($this->whenLoaded('products')),
        ];
    }
}
