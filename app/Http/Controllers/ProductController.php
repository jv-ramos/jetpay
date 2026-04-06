<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use AuthorizesRequests;
    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Listar produtos",
     *     tags={"Products"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Lista de produtos")
     * )
     */
    public function index()
    {
        return ProductResource::collection(Product::paginate(10)); //latest()->take(50)->get());
    }

    /**
     * @OA\Post(
     *      path="/products",
     *      summary="Criar produto",
     *      tags={"Products"},
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *          required={"name","amount"},
     *          @OA\Property(property="name", type="string", example="Produto A"),
     *          @OA\Property(property="amount", type="integer", example=100)
     *          )
     *      ),
     *      @OA\Response(response=201, description="Produto criado"),
     *      @OA\Response(response=422, description="Erro de validação")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/products/{id}",
     *      summary="Exibir produto",
     *      tags={"Products"},
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID do produto",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Produto encontrado"),
     *      @OA\Response(response=404, description="Produto não encontrado")
     * )
     */
    public function show(Product $product)
    {
        return ProductResource::make($product);
    }

    /**
     * @OA\Put(
     *      path="/products/{id}",
     *      summary="Atualizar produto",
     *      tags={"Products"},
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID do produto",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="Produto A"),
     *              @OA\Property(property="amount", type="integer", example=100)
     *          )
     *      ),
     *      @OA\Response(response=200, description="Produto atualizado"),
     *      @OA\Response(response=422, description="Erro de validação"),
     *      @OA\Response(response=404, description="Produto não encontrado")
     * )
     */
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

    /**
     * @OA\Delete(
     *      path="/products/{id}",
     *      summary="Excluir produto",
     *      tags={"Products"},
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID do produto",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=204, description="Produto excluído"),
     *      @OA\Response(response=404, description="Produto não encontrado")
     * )
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', Product::class);

        $product->delete();

        return response()->noContent();
    }
}
