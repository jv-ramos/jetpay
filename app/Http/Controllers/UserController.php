<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/users',
        description: 'Get a list of users',
        summary: 'Get users',
        security: [['sanctum' => []]],
        tags: ['Users'],
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
                                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                                    new OA\Property(property: 'email', type: 'string', example: 'johndoe@jetpay.com'),
                                    new OA\Property(property: 'role', type: 'string', example: 'admin')
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
        $this->authorize('viewAny', User::class);

        return UserResource::collection(User::paginate(10));
    }

    #[OA\Put(
        path: '/users/{id}',
        description: 'Update a user',
        summary: 'Update user',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Updated User'),
                        new OA\Property(property: 'email', type: 'string', example: 'updateduser@jetpay.com')
                    ]
                )
            )
        ]
    )]
    public function update(Request $request, string $id)
    {
        $this->authorize('update', User::class);

        $request->validate([
            'name' => 'string',
            'email' => 'email',
        ]);

        User::where('id', $id)->update($request->only(['name', 'email']));

        return response()->json([
            'message' => 'User updated successfully',
        ]);
    }

    #[OA\Delete(
        path: '/users/{id}',
        description: 'Delete a user',
        summary: 'Delete user',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'User deleted successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function destroy(string $id)
    {
        $this->authorize('delete', User::class);

        User::destroy($id);

        return response()->json([
            'message' => 'User deleted successfully',
        ], 204);
    }
}
