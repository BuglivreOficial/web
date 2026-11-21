<?php

/**
 * @var \Core\Http\Routing $route
 */

use App\Controllers\Api\AuthController;

$route->post('/auth/login', [AuthController::class, 'login']);
$route->post('/auth/register', [AuthController::class, 'register']);
