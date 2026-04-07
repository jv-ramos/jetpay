<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    use AuthorizesRequests;

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
                                    new OA\Property(property: 'amount', type: 'integer', example: 100)
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function index()
    {
        return ProductResource::collection(Product::paginate(10)); //latest()->take(50)->get());
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
                        new OA\Property(property: 'amount', type: 'integer', example: 100)
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
            )
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|integer|min:1'
        ]);

        $created = Product::create($validated);

        return new ProductResource($created);
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
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Produto A'),
                        new OA\Property(property: 'amount', type: 'integer', example: 100)
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
            )
        ]
    )]
    public function show(Product $product)
    {
        return ProductResource::make($product);
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
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Produto A'),
                        new OA\Property(property: 'amount', type: 'integer', example: 100)
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
            )
        ]
    )]
    public function update(Request $request, Product $product)
    {
        $this->authorize('update', Product::class);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|integer|min:1'
        ]);

        $product->update($validated);

        return new ProductResource($product);
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
            )
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
            )
        ]
    )]
    public function destroy(Product $product)
    {
        $this->authorize('delete', Product::class);

        $product->delete();

        return response()->noContent();
    }
}
