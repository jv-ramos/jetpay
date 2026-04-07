<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ClientController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/clients',
        description: 'Get a list of clients',
        summary: 'Get clients',
        security: [['sanctum' => []]],
        tags: ['Clients'],
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
                                    new OA\Property(property: 'name', type: 'string', example: 'Cliente A'),
                                    new OA\Property(property: 'email', type: 'string', example: 'clientea@jetpay.com'),
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
        $this->authorize('viewAny', Client::class);

        return ClientResource::collection(Client::paginate(10));
    }

    #[OA\Post(
        path: '/clients',
        description: 'Create a new client',
        summary: 'Create a new client',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Cliente A'),
                        new OA\Property(property: 'email', type: 'string', example: 'clientea@jetpay.com'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
        ]);

        $client = Client::create($validated);

        return new ClientResource($client);
    }

    #[OA\Get(
        path: '/clients/{id}',
        description: 'Get a client by ID',
        summary: 'Get client',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Client ID',
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
                        new OA\Property(property: 'name', type: 'string', example: 'Cliente A'),
                        new OA\Property(property: 'email', type: 'string', example: 'clientea@jetpay.com'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Client not found'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load('transactions');
        $client->load('transactions.products');

        return new ClientResource($client);
    }

    #[OA\Put(
        path: '/clients/{id}',
        description: 'Update a client',
        summary: 'Update client',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Client ID',
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
                        new OA\Property(property: 'name', type: 'string', example: 'Cliente A'),
                        new OA\Property(property: 'email', type: 'string', example: 'clientea@jetpay.com'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request'
            ),
        ]
    )]
    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:clients,email,' . $client->id,
        ]);

        $client->update($validated);

        return new ClientResource($client);
    }

    #[OA\Delete(
        path: '/clients/{id}',
        description: 'Delete a client',
        summary: 'Delete client',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Client ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Client deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Client not found'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return response()->noContent();
    }
}
