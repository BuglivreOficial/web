<?php

/**
 * @var \Core\Http\Routing $route
 */

use App\Controllers\Api\AuthController;
use App\Controllers\Api\CategoryController;

// --- Autenticação -----------------------------------------------------------
$route->post('/auth/login', [AuthController::class, 'login']);
$route->post('/auth/register', [AuthController::class, 'register']);

// --- Categorias (restrito ao painel administrativo) -------------------------
$route->get('/categories', [CategoryController::class, 'index']);
$route->post('/categories', [CategoryController::class, 'store']);
$route->get('/categories/{id:int}', [CategoryController::class, 'show']);
$route->put('/categories/{id:int}', [CategoryController::class, 'update']);
$route->delete('/categories/{id:int}', [CategoryController::class, 'destroy']);
