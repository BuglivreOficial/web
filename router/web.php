<?php

use Pecee\SimpleRouter\SimpleRouter as Router;

use App\Controller\HomeController;

Router::get('/', [HomeController::class, 'index']);
