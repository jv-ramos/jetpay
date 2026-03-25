<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    use AuthorizesRequests;
    /**
     * Handle the incoming request.
     */
    public function index()
    {
        $this->authorize('viewAny', Client::class);

        return ClientResource::collection(Client::paginate(10));
    }

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

    public function show(Client $client)
    {
        $client->load('transactions');
        $client->load('transactions.products');

        return new ClientResource($client);
    }
}
