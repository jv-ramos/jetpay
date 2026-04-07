<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthenticatedSessionController extends Controller
{

    #[OA\Post(
        path: '/login',
        description: 'Authenticate user and return API token',
        summary: 'Login',
        security: [],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'your-api-token-here')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $token = $request->user()->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    /**
     * Destroy an authenticated session.
     */
    // public function destroy(Request $request): Response
    // {
    //     Auth::guard('web')->logout();
    //
    //     $request->session()->invalidate();
    //
    //     $request->session()->regenerateToken();
    //
    //     return response()->noContent();
    // }
}
