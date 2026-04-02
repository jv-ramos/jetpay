<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\Gateway\GatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *      path="/api/transactions",
     *      summary="Listar transações",
     *      tags={"Transactions"},
     *      security={{"sanctum": {}}},
     *      @OA\Response(response=200, description="Lista de transações"),
     *      @OA\Response(response=401, description="Não autorizado")
     *  )
     */
    public function index()
    {
        return TransactionResource::collection(Transaction::paginate(10));
    }

    /**
     * @OA\Post(
     *     path="/api/transactions",
     *     summary="Criar transação",
     *     tags={"Transactions"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"client_id","name","email","card_number","cvv","cart"},
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="card_number", type="string", example="5569000000006063"),
     *             @OA\Property(property="cvv", type="string", example="010"),
     *             @OA\Property(property="cart", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Transação criada"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
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
     * @OA\Get(
     *      path="/api/transactions/{id}",
     *      summary="",
     *      tags={"Transactions"},
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID da transação",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Detalhes da transação"),
     *      @OA\Response(response=404, description="Transação não encontrada")
     * )
     */
    public function show(Transaction $transaction)
    {
        $transaction->load('products');
        return new TransactionResource($transaction);
    }

    /**
     * @OA\Post(
     *      path="/api/transactions/{id}/refund",
     *      summary="Reembolsar transação",
     *      tags={"Transactions"},
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID da transação",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Transação reembolsada"),
     *      @OA\Response(response=422, description="Transação já reembolsada"),
     *      @OA\Response(response=404, description="Transação não encontrada")
     * )
     */
    public function refund(string $id)
    {
        $this->authorize('refund', Transaction::class);

        $transaction = Transaction::findOrFail($id);

        if ($transaction->status === 'charged_back') {
            return response()->json(['message' => 'Transaction already refunded.'], 422);
        }

        $gatewayService = GatewayFactory::make($transaction->gateway);
        $gatewayResponse = $gatewayService->refund($transaction);

        $transaction->update(['status' => $gatewayResponse['status']]);

        return new TransactionResource($transaction);
    }
}
