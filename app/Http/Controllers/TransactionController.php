<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\Gateway\GatewayFactory;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TransactionResource::collection(Transaction::paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'         => 'required|integer|exists:clients,id',
            'name'              => 'required|string|max:255',
            'email'             => 'required|email',
            'card_number'       => 'required|string|size:16',
            'cvv'               => 'required|string|min:3|max:4',
            'cart'              => 'required|array|min:1',
            'cart.*.product_id' => 'required|integer|exists:products,id',
            'cart.*.quantity'   => 'required|integer|min:1',
        ]);

        // Calculate total amount
        $amount = collect($validated['cart'])->reduce(function ($total, $item) {
            $product = Product::find($item['product_id']);
            return $total + ($product->amount * $item['quantity']);
        }, 0);

        // Process payment through the active gateway
        $selectedGateway = Gateway::where('is_active', true)->orderBy('priority')->first();
        $gatewayService = GatewayFactory::make($selectedGateway);
        $gatewayResponse = $gatewayService->createTransaction(array_merge($validated, ['amount' => $amount]));

        // Create transaction record
        $transaction = Transaction::create([
            'client_id'         => $validated['client_id'],
            'gateway_id'        => $selectedGateway->id,
            'external_id'       => $gatewayResponse['id'],
            'status'            => $gatewayResponse['status'],
            'amount'            => $amount,
            'card_last_numbers' => substr($validated['card_number'], -4),
        ]);

        // Mount products cart
        $cart = collect($validated['cart'])->mapWithKeys(fn($item) => [
            $item['product_id'] => ['quantity' => $item['quantity']]
        ]);

        $transaction->products()->attach($cart);
        $transaction->load('products'); // Eager load products for the response

        return new TransactionResource($transaction);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $transaction->load('products');
        return new TransactionResource($transaction);
    }

    /**
     * Update the specified resource from storage.
     */
    public function refund(string $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status === 'charged_back') {
            return response()->json(['message' => 'Transaction already refunded.'], 422);
        }

        $gatewayService = GatewayFactory::make($transaction->gateway);
        $gatewayResponse = $gatewayService->refund($transaction->external_id);

        $transaction->update(['status' => $gatewayResponse['status']]);

        return new TransactionResource($transaction);
    }
}
