<?php

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('User Resource', function () {
    uses(RefreshDatabase::class);

    it('should return the correct user data in the resource', function () {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'role' => 'ADMIN',
        ]);

        $resource = new UserResource($user);
        $data = $resource->toArray(request());

        expect($data)->toHaveKey('id');
        expect($data['name'])->toBe('John Doe');
        expect($data['email'])->toBe('johndoe@example.com');
        expect($data['role'])->toBe('ADMIN');
        expect($data)->not()->toHaveKey('created_at');
        expect($data)->not()->toHaveKey('updated_at');
    });
});
