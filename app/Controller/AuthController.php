<?php
namespace App\Controller;

use Core\Http\Response;

class AuthController {
    public function login() {
        Response::render('app/login/index.html', []);
    }

    public function register() {
        Response::render('app/register/index.html', []);
    }

    public function resetPassword() {
        Response::render('app/reset-password/index.html', []);
    }
}