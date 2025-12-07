<?php

use Pecee\SimpleRouter\SimpleRouter as Router;

use App\Controller\HomeController;
use App\Controller\AuthController;

Router::get('/', [HomeController::class, 'index']);

Router::get('/login', [AuthController::class, 'login']);
Router::get('/register', [AuthController::class, 'register']);
Router::get('/reset-password', [AuthController::class, 'resetPassword']);
