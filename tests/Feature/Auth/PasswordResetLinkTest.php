<?php

use App\Models\User;

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
            'email' => 'newuser@example.com'
        ]);

        $response->assertStatus(422);
    });
});
