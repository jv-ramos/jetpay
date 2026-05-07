<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Product\ProductServices;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    use AuthorizesRequests;

    private ProductServices $service;

    public function __construct(ProductServices $service)
    {
        $this->service = $service;
    }

    #[OA\Get(
        path: '/products',
        description: 'Get a list of products',
        summary: 'Get products',
        security: [['sanctum' => []]],
        tags: ['Products'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Produto A'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Produto A is really good'),
                                    new OA\Property(property: 'amount', type: 'integer', example: 10000),
                                    new OA\Property(property: 'quantity', type: 'integer', example: 100),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function index()
    {
        return $this->service->indexProducts();
    }

    #[OA\Post(
        path: '/products',
        description: 'Create a new product',
        summary: 'Create product',
        security: [['sanctum' => []]],
        tags: ['Products'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Product created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Produto A'),
                        new OA\Property(property: 'description', type: 'string', example: 'Produto A is really good'),
                        new OA\Property(property: 'amount', type: 'integer', example: 10000),
                        new OA\Property(property: 'quantity', type: 'integer', example: 100),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'name' => 'required|unique:products,name|string|min:3|max:50',
            'description' => 'string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'quantity' => 'required|integer|min:0',
        ]);

        $this->service->storeProduct($validated);
    }

    #[OA\Get(
        path: '/products/{id}',
        description: 'Get a product by ID',
        summary: 'Get product',
        security: [['sanctum' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Produto A'),
                        new OA\Property(property: 'description', type: 'string', example: 'Produto A is really good'),
                        new OA\Property(property: 'amount', type: 'integer', example: 10000),
                        new OA\Property(property: 'quantity', type: 'integer', example: 100),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function show()
    {
        return $this->service->getProduct();
    }

    #[OA\Put(
        path: '/products/{id}',
        description: 'Update a product',
        summary: 'Update product',
        security: [['sanctum' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Produto A'),
                        new OA\Property(property: 'description', type: 'string', example: 'Produto A is really good'),
                        new OA\Property(property: 'amount', type: 'integer', example: 10000),
                        new OA\Property(property: 'quantity', type: 'integer', example: 100),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function update(Request $request)
    {
        $this->authorize('update', Product::class);

        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'quantity' => 'sometimes|integer|min:0',
        ]);

        return $this->service->updateProduct($validated);
    }

    #[OA\Delete(
        path: '/products/{id}',
        description: 'Delete a product',
        summary: 'Delete product',
        security: [['sanctum' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Product deleted'
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function destroy()
    {
        $this->authorize('delete', Product::class);

        $query = substr(url()->full(), strpos(url()->full(), 'products/') + strlen('products/'));
        $response = Http::withHeaders(['accept' => 'application/json'])->delete(config('api.product_api_url').$query);

        return $response;
    }

    public function stockUpdate(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'required|boolean',
            'quantity' => 'required|int',
        ]);

        return $this->service->updateProductStock($validated);
    }
}
