<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Auth\Guard;

class DashboardController extends BaseController
{
    /**
     * GET /admin
     * Página inicial do painel — restrita a usuários autenticados.
     */
    public function index()
    {
        $guard = new Guard();
        $user  = $guard->user();

        // Somente usuários com papel autorizado acessam o painel.
        if (!$guard->allowsPanel($user)) {
            $this->response->redirect('/admin/auth');
        }

        $this->response->view('admin/dashboard', [
            'active' => 'dashboard',
            'user'   => $user,
        ]);
    }
}
