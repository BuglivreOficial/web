<?php

use App\Controller\AuthController;
use Pecee\SimpleRouter\SimpleRouter as Router;

Router::post('/register', [AuthController::class, 'create']);