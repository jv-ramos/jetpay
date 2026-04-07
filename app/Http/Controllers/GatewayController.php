<?php

namespace App\Http\Controllers;

use App\Http\Resources\GatewayResource;
use App\Models\Gateway;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class GatewayController extends Controller
{
    #[OA\Patch(
        path: '/gateways',
        description: 'Toggle gateways on and off',
        summary: 'Toggle gateway',
        security: [['sanctum' => []]],
        tags: ['Gateways'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Gateway A'),
                        new OA\Property(property: 'is_active', type: 'boolean', example: true),
                        new OA\Property(property: 'priority', type: 'integer', example: 1)
                    ]
                )
            ),
        ]
    )]
    public function toggle(Gateway $gateway)
    {
        if (Gateway::where('is_active', true)->count() <= 1 && $gateway->is_active) {
            return response()->json(['message' => 'At least one gateway must be active.'], 400);
        }

        $gateway->update(['is_active' => !$gateway->is_active]);
        return GatewayResource::make($gateway);
    }

    #[OA\Patch(
        path: '/gateways/priority',
        description: 'Update gateway priority',
        summary: 'Update gateway priority',
        security: [['sanctum' => []]],
        tags: ['Gateways'],
        parameters: [
            new OA\Parameter(
                name: 'priority',
                in: 'query',
                description: 'New priority for the gateway',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Gateway A'),
                        new OA\Property(property: 'is_active', type: 'boolean', example: true),
                        new OA\Property(property: 'priority', type: 'integer', example: 1)
                    ]
                )
            ),
        ]
    )]
    public function updatePriority(Request $request, Gateway $gateway)
    {
        $request->validate(['priority' => 'required|integer|min:1']);
        $gateway->update(['priority' => $request->priority]);
        return GatewayResource::make($gateway);
    }
}
