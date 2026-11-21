<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Auth\Guard;

class AuthController extends BaseController
{
    /**
     * GET /admin/auth
     * Exibe o formulário de login (ou redireciona quem já está autenticado).
     */
    public function index()
    {
        $guard = new Guard();

        if ($guard->allowsPanel($guard->user())) {
            $this->response->redirect('/admin');
        }

        $this->response->view('admin/login');
    }

    /**
     * GET /admin/logout
     * Encerra a sessão do painel e volta para o login.
     */
    public function logout()
    {
        (new Guard())->logout();

        $this->response->redirect('/admin/auth');
    }
}
