<?php

namespace App\Services\Product;

use Illuminate\Support\Facades\Http;

class ProductServices
{
    public function indexProducts(): array
    {
        $query = strstr(url()->full(), '?');
        $response = Http::withHeaders(['accept' => 'application/json'])->get(config('api.product_api_url').$query);

        return $response['data'];
    }

    public function storeProduct(array $validated): void
    {
        Http::withHeaders(['accept' => 'application/json'])->post(config('api.product_api_url'), $validated);
    }

    public function getProduct($query = null)
    {
        if (is_null($query)) {
            $query = substr(url()->full(), strpos(url()->full(), 'products/') + strlen('products/'));
        }
        $response = Http::withHeaders(['accept' => 'application/json'])->get(config('api.product_api_url').'/'.$query);

        return $response['data'] ?? $response;
    }

    public function updateProduct(array $validated)
    {
        $query = substr(url()->full(), strpos(url()->full(), 'products/') + strlen('products/'));
        Http::withHeaders(['accept' => 'application/json'])->put(config('api.product_api_url').'/'.$query, $validated);
    }

    public function updateProductStock(array $validated, $query = null)
    {
        if (isset($query)) {
            $query = 'stockUpdate/'.$query;
        }

        if (is_null($query)) {
            $query = substr(url()->full(), strpos(url()->full(), 'products/') + strlen('products/'));
        }

        $response = Http::withHeaders(['accept' => 'application/json'])->post(config('api.product_api_url').'/'.$query, $validated);
        if ($response->failed()) {
            $statusCode = $response->status();
            $errorMessage = $response->json('message', 'Erro desconhecido');

            throw new \Exception($errorMessage, $statusCode);
        }

        $data = $response->collect();

        return $data;
    }
}
