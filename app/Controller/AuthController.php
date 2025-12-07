<?php
namespace App\Controller;

use App\Model\Register;
use Appwrite\AppwriteException;
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

    public function create() {
            $register = new Register('teste', 'teste@gmail.com', '123456');
            var_dump($register->create());
    
    }
}