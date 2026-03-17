<?php

namespace App\Http\Controllers;

use App\Http\Resources\GatewayResource;
use App\Models\Gateway;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function toggle(Gateway $gateway)
    {
        $gateway->update(['is_active' => !$gateway->is_active]);
        return GatewayResource::make($gateway);
    }

    public function updatePriority(Request $request, Gateway $gateway)
    {
        $request->validate(['priority' => 'required|integer|min:1']);
        $gateway->update(['priority' => $request->priority]);
        return GatewayResource::make($gateway);
    }
}
