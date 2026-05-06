<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Gateway;
use App\Models\Transaction;
use App\Services\Gateway\GatewayFactory;
use App\Services\Product\ProductServices;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    protected ProductServices $productService;

    public function __construct(ProductServices $productService)
    {
        $this->productService = $productService;
    }

    #[OA\Get(
        path: '/transactions',
        description: 'Retorna uma lista paginada de transações',
        summary: 'Listar transações',
        security: [['sanctum' => []]],
        tags: ['Transactions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de transações',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'client_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'gateway_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'external_id', type: 'string', example: 'abc123'),
                                    new OA\Property(property: 'status', type: 'string', example: 'paid'),
                                    new OA\Property(property: 'amount', type: 'integer', example: 1000),
                                    new OA\Property(property: 'card_last_numbers', type: 'string', example: '6063'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Não autorizado'),
        ]
    )]
    public function index()
    {
        return TransactionResource::collection(Transaction::paginate(10));
    }

    public function reserveOrReplenishProductStock(array $order, int $id)
    {
        $this->productService->updateProductStock($order, $id);
    }

    #[OA\Post(
        path: '/transactions',
        description: 'Cria uma nova transação',
        summary: 'Criar transação',
        security: [['sanctum' => []]],
        tags: ['Transactions'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Transação criada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'client_id', type: 'integer', example: 1),
                        new OA\Property(property: 'gateway_id', type: 'integer', example: 1),
                        new OA\Property(property: 'external_id', type: 'string', example: 'abc123'),
                        new OA\Property(property: 'status', type: 'string', example: 'paid'),
                        new OA\Property(property: 'amount', type: 'integer', example: 1000),
                        new OA\Property(property: 'card_last_numbers', type: 'string', example: '6063'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
                        new OA\Property(
                            property: 'products',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Product A'),
                                    new OA\Property(property: 'amount', type: 'integer', example: 500),
                                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                ]
                            )
                        ),
                    ]
                )
            ),
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'card_number' => 'required|string|size:16',
            'cvv' => 'required|string|min:3|max:4',
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|integer',
            'cart.*.quantity' => 'required|integer|min:1',
        ]);

        $reserveProduct = collect();

        collect($validated['cart'])->sum(function ($item) use (&$reserveProduct) {
            $product = $this->productService->getProduct($item['product_id']);
            $order = ['operation' => false, 'quantity' => $item['quantity']];

            $this->reserveOrReplenishProductStock($order, $item['product_id']);

            $reserveInstance = collect([
                'product_id' => $item['product_id'],
                'name' => $product['name'],
                'amount' => $product['amount'],
                'quantity' => $item['quantity'],
            ]);

            $reserveProduct->push($reserveInstance);
        });

        $amountTotal = collect($reserveProduct)->reduce(function ($total, $item) {
            if (is_float($item['amount'])) {
                $item['amount'] = (int) ($item['amount'] * 100);
            }
            return $total + ($item['amount'] * $item['quantity']);
        });

        // Process payment through the active gateway
        $numberOfTries = 1;
        $maxNumberOfTries = Gateway::where('is_active', true)->count();
        $selectedGateway = Gateway::where('is_active', true)->orderBy('priority')->first();
        $gatewayService = GatewayFactory::make($selectedGateway);
        $gatewayResponse = $gatewayService->createTransaction(array_merge($validated, ['amount' => $amountTotal]));
        while ($numberOfTries <= $maxNumberOfTries && ! $gatewayResponse || ! $gatewayResponse['status']) {
            $selectedGateway = Gateway::where('is_active', true)->orderBy('priority')->skip($numberOfTries)->first();
            $gatewayService = GatewayFactory::make($selectedGateway);
            $gatewayResponse = $gatewayService->createTransaction(array_merge($validated, ['amount' => $amountTotal]));
            $numberOfTries++;
        }

        if (! $gatewayResponse || ! $gatewayResponse['status']) {
            $reserveProduct->map(function ($item) {
                $replenish = [
                    'operation' => true,
                    'quantity' => $item['quantity'],
                ];
                $this->reserveOrReplenishProductStock($replenish, $item->get('product_id'));
            });

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
            ]);
        }

        $cart = collect($reserveProduct)->map(fn ($item) => [
            'product_id' => $item['product_id'],
            'name' => $item['name'],
            'amount' => $item['amount'],
            'quantity' => $item['quantity'],
        ]);

        // Create transaction record
        $transaction = Transaction::create([
            'client_id' => $validated['client_id'],
            'gateway_id' => $selectedGateway->id,
            'external_id' => $gatewayResponse['id'],
            'status' => $gatewayResponse['status'],
            'amount' => $amountTotal,
            'card_last_numbers' => substr($validated['card_number'], -4),
            'order' => $cart->toJson(),
        ]);

        // Mount products cart
        // $cart = collect($validated['cart'])->mapWithKeys(fn ($item) => [
        //     $item['product_id'] => ['quantity' => $item['quantity']],
        // ]);
        //
        // $transaction->attach($cart);
        // $transaction->load('products'); // Eager load products for the response
        return new TransactionResource($transaction);
    }

    #[OA\Get(
        path: '/transactions/{id}',
        description: 'Exibe os detalhes de uma transação específica',
        summary: 'Exibir transação',
        security: [['sanctum' => []]],
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID da transação',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalhes da transação',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'client_id', type: 'integer', example: 1),
                        new OA\Property(property: 'gateway_id', type: 'integer', example: 1),
                        new OA\Property(property: 'external_id', type: 'string', example: 'abc123'),
                        new OA\Property(property: 'status', type: 'string', example: 'paid'),
                        new OA\Property(property: 'amount', type: 'integer', example: 1000),
                        new OA\Property(property: 'card_last_numbers', type: 'string', example: '6063'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
                        new OA\Property(
                            property: 'products',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Product A'),
                                    new OA\Property(property: 'amount', type: 'integer', example: 500),
                                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Transação não encontrada'),
        ]

    )]
    public function show(Transaction $transaction)
    {
        // $transaction->load('products');

        return new TransactionResource($transaction);
    }

    #[OA\Post(
        path: '/transactions/{id}/refund',
        description: 'Realiza o estorno de uma transação',
        summary: 'Estornar transação',
        security: [['sanctum' => []]],
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID da transação a ser estornada',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Transação estornada com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'client_id', type: 'integer', example: 1),
                        new OA\Property(property: 'gateway_id', type: 'integer', example: 1),
                        new OA\Property(property: 'external_id', type: 'string', example: 'abc123'),
                        new OA\Property(property: 'status', type: 'string', example: 'charged_back'),
                        new OA\Property(property: 'amount', type: 'integer', example: 1000),
                        new OA\Property(property: 'card_last_numbers', type: 'string', example: '6063'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Transação já estornada'),
        ]
    )]
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
