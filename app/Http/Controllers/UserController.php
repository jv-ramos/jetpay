<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        return UserResource::collection(User::paginate(10));
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete', User::class);

        User::destroy($id);

        return response()->json([
            'message' => 'User deleted successfully',
        ], 204);
    }
}
