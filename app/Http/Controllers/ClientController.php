<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;

class ClientController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        return ClientResource::collection(Client::paginate(10));
    }
}
