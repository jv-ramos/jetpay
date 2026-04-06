<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="JetPay API",
 *     version="1.0.0",
 *     description="API de gateway de pagamento"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer"
 * )
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 */
abstract class Controller
{
    //
}
