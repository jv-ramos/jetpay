<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

class RegisteredUserController extends Controller
{
    use AuthorizesRequests;
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */

    #[OA\Post(
        path: '/register',
        description: 'Register a new user',
        summary: 'Register',
        security: [['sanctum' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation'
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function store(Request $request): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:' . implode(',', array_column(\App\Role::cases(), 'value'))],
        ]);

        $this->authorize('create', User::class);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'role' => $request->string('role'),
        ]);

        event(new Registered($user));

        // Auth::login($user);

        return response()->noContent();
    }
}
