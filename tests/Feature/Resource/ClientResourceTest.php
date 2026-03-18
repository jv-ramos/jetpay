<?php

use App\Http\Resources\ClientResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Client Resource', function () {

    uses(RefreshDatabase::class);

    it('returns the correct structure', function () {
        $client = \App\Models\Client::create([
            'name' => 'Client 1',
            'email' => 'client@example.com'
        ]);

        $clientResource = new ClientResource($client);

        expect($clientResource->toArray(request()))->toEqual([
            'name' => 'Client 1',
            'email' => 'client@example.com'
        ]);
    });
});
