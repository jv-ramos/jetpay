<?php

namespace App\Http\Controllers;

use App\Http\Resources\GatewayResource;
use App\Models\Gateway;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function toggle(Gateway $gateway)
    {
        if (Gateway::where('is_active', true)->count() <= 1 && $gateway->is_active) {
            return response()->json(['message' => 'At least one gateway must be active.'], 400);
        }

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
