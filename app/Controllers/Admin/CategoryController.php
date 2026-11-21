<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Category;
use Core\Auth\Guard;
use PDO;

class CategoryController extends BaseController
{
    /**
     * GET /admin/categorias
     * Lista as categorias — restrita a usuários autenticados no painel.
     * O CRUD é realizado via API (fetch), sem recarregar a página.
     */
    public function index(PDO $db)
    {
        $guard = new Guard();
        $user  = $guard->user();

        if (!$guard->allowsPanel($user)) {
            $this->response->redirect('/admin/auth');
        }

        $this->response->view('admin/categorias', [
            'active'     => 'categorias',
            'user'       => $user,
            'categories' => (new Category($db))->all(),
        ]);
    }
}
