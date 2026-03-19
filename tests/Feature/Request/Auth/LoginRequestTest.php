<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

describe('PasswordResetLinkController', function () {
    it('sends a password reset link to the user', function () {
        $user = User::factory()->create();

        $response = $this->postJson(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
    });

    it('returns an error if the email is invalid', function () {
        $response = $this->postJson(route('password.email'), [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422);
    });

    it('returns an error if the email does not exist', function () {
        $response = $this->postJson(route('password.email'), [
            'email' => ''
        ]);

        $response->assertStatus(422);
    });

    it('should throw validation exception when rate limited', function () {
        $user = User::factory()->create();

        $key = Str::transliterate(Str::lower($user->email) . '|127.0.0.1');
        foreach (range(1, 5) as $i) {
            RateLimiter::hit($key);
        }

        $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['email']]);
    });
});
