<?php

namespace App\Services\Transaction;

use App\Models\Gateway;
use App\Models\Transaction;
use App\Services\Gateway\GatewayFactory;
use App\Services\Product\ProductServices;
use Illuminate\Support\Collection;
use Throwable;

class TransactionServices
{
    public function __construct(
        private ProductServices $productService
    ) {}

    public function processTransaction(array $processedGateway, Collection $reserveProduct, array $validated, int $totalAmount)
    {
        $reservedProducts = collect();
        try {
            $reservedProducts = $this->reserveStock($reserveProduct);
        } catch (Throwable $e) {
            if (! $reservedProducts->isEmpty()) {
                $this->releaseStock($reservedProducts);
            }

            $cart = collect($reserveProduct)->map(fn($item) => [
                'product_id' => $item['id'],
                'name' => $item['name'],
                'amount' => $item['amount'],
                'quantity' => $item['quantity'],
            ]);

            $transaction = Transaction::create([
                'client_id' => $validated['client_id'],
                'gateway_id' => $processedGateway['selected_gateway_id'],
                'external_id' => $processedGateway['external_id'],
                'status' => $processedGateway['status'],
                'amount' => $totalAmount,
                'card_last_numbers' => substr($validated['card_number'], -4),
                'order' => $cart->toJson(),
            ]);
            $this->checkTransactionStatusAndRefundIfNeeded($transaction->id);
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $cart = collect($reservedProducts)->map(fn($item) => [
            'product_id' => $item['id'],
            'name' => $item['name'],
            'amount' => $item['amount'],
            'quantity' => $item['quantity'],
        ]);

        return Transaction::create([
            'client_id' => $validated['client_id'],
            'gateway_id' => $processedGateway['selected_gateway_id'],
            'external_id' => $processedGateway['external_id'],
            'status' => $processedGateway['status'],
            'amount' => $totalAmount,
            'card_last_numbers' => substr($validated['card_number'], -4),
            'order' => $cart->toJson(),
        ]);
    }

    public function processGateway(array $validated, int $totalAmount)
    {
        $numberOfTries = 0;
        $maxNumberOfTries = Gateway::where('is_active', true)->count();
        $selectedGateway = Gateway::where('is_active', true)->orderBy('priority')->first();
        $gatewayService = null;
        $gatewayResponse = null;

        while (
            $numberOfTries <= $maxNumberOfTries && (
                ! $gatewayResponse ||
                ! isset($gatewayResponse['status']) ||
                $gatewayResponse['status'] !== 'paid'
            )
        ) {
            $selectedGateway = Gateway::where('is_active', true)
                ->orderBy('priority')
                ->skip($numberOfTries)
                ->first();

            if (! $selectedGateway) {
                $numberOfTries++;
                continue;
            }

            $gatewayService = GatewayFactory::make($selectedGateway);

            if (! $gatewayService) {
                $numberOfTries++;
                continue;
            }

            $gatewayResponse = $gatewayService
                ->createTransaction(array_merge(
                    $validated,
                    ['amount' => $totalAmount]
                ));

            if (
                ! isset($gatewayResponse['status']) ||
                $gatewayResponse['status'] !== 'paid'
            ) {
                $numberOfTries++;
                continue;
            }
        }

        if (
            ! isset($gatewayResponse['status']) ||
            $gatewayResponse['status'] !== 'paid'
        ) {
            throw new \Exception('All gateways failed to process the transaction', 502);
        }

        return [
            'selected_gateway_id' => $selectedGateway->id,
            'external_id' => $gatewayResponse['id'],
            'status' => $gatewayResponse['status'],
        ];
    }

    public function calculateTotalAmount(Collection $cart): int
    {
        return $cart->reduce(fn($total, $item) => $total + ($item['amount'] * $item['quantity']), 0);
    }

    public function checkTransactionStatusAndRefundIfNeeded(string $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status === 'charged_back') {
            return response()->json(['message' => 'Transaction already refunded.'], 422);
        }

        $gatewayService = GatewayFactory::make($transaction->gateway);
        $gatewayResponse = $gatewayService->refund($transaction);

        $transaction->update(['status' => $gatewayResponse['status']]);

        return $transaction;
    }

    public function reserveStock(Collection $cart): Collection
    {
        $collection = collect();

        foreach ($cart as $item) {
            $order = ['operation' => false, 'quantity' => $item['quantity']];

            $reservedItem = $this->reserveOrReleaseStock($order, $item['id']);

            $collection->push($reservedItem);
        }
        return $collection;
    }

    public function releaseStock(Collection $cart): Collection
    {
        $collection = collect();

        foreach ($cart as $item) {
            $order = ['operation' => true, 'quantity' => $item['quantity']];

            $releasedItem = $this->reserveOrReleaseStock($order, $item['id']);

            $collection->push($releasedItem);
        }
        return $collection;
    }

    public function checkStockAvailabilityAndReturnProductCollection(array $cart)
    {
        $collection = collect();
        foreach ($cart as $item) {
            $product = $this->productService->getProduct($item['product_id']);
            if ($product['quantity'] < $item['quantity']) {
                throw new \Exception(
                    "Only {$product['quantity']} {$product['name']} left in stock",
                    400
                );
            }
            $collection->push($product);
        }
        return $collection;
    }

    public function reserveOrReleaseStock(array $order, int $productId): Collection
    {
        return $this->productService->updateProductStock($order, $productId);
    }
}
