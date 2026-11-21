<?php

use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\DashboardController;

/**
 * @var \Core\Http\Routing $route
 */

// --- Painel administrativo -------------------------------------------------
$route->get('/admin/auth', [AdminAuthController::class, 'index']);
$route->get('/admin/logout', [AdminAuthController::class, 'logout']);
$route->get('/admin', [DashboardController::class, 'index']);