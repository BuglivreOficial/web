<?php
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Category;
use Core\Auth\Guard;
use Core\Log\Logger;
use PDO;

class CategoryController extends BaseController
{
    /**
     * GET /api/categories
     * Lista todas as categorias cadastradas.
     */
    public function index(PDO $db)
    {
        $this->authorize();

        $categories = array_map([$this, 'present'], (new Category($db))->all());

        return $this->response->json([
            'total'      => count($categories),
            'categories' => $categories,
        ]);
    }

    /**
     * GET /api/categories/{id}
     * Exibe uma categoria específica.
     */
    public function show(PDO $db, int $id)
    {
        $this->authorize();

        $category = (new Category($db))->findById($id);

        if (!$category) {
            return $this->response->json(['message' => 'Categoria não encontrada.'], 404);
        }

        return $this->response->json(['category' => $this->present($category)]);
    }

    /**
     * POST /api/categories
     * Cria uma nova categoria.
     */
    public function store(PDO $db)
    {
        $this->authorize();

        if ($this->validation->rules(['name' => 'required|min:3'])) {
            return $this->response->json([
                'message' => 'Os dados informados são inválidos.',
                'errors'  => $this->validation->getErros(),
            ], 422);
        }

        $name = trim((string) $this->request->body('name'));
        $slug = (string) $this->slug($name);

        $categories = new Category($db);

        if ($categories->findBySlug($slug)) {
            return $this->response->json([
                'message' => 'Os dados informados são inválidos.',
                'errors'  => ['slug' => ['Já existe uma categoria com este identificador.']],
            ], 422);
        }

        $category = $categories->create([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $this->description(),
            'status'      => $this->status(),
        ]);

        (new Logger('category'))->info('Categoria criada: {name}', [
            'category_id' => (int) $category['id'],
            'name'        => $category['name'],
        ]);

        return $this->response->json([
            'message'  => 'Categoria criada com sucesso.',
            'category' => $this->present($category),
        ], 201);
    }

    /**
     * PUT /api/categories/{id}
     * Atualiza uma categoria existente.
     */
    public function update(PDO $db, int $id)
    {
        $this->authorize();

        $categories = new Category($db);
        $category   = $categories->findById($id);

        if (!$category) {
            return $this->response->json(['message' => 'Categoria não encontrada.'], 404);
        }

        if ($this->validation->rules(['name' => 'required|min:3'])) {
            return $this->response->json([
                'message' => 'Os dados informados são inválidos.',
                'errors'  => $this->validation->getErros(),
            ], 422);
        }

        $name = trim((string) $this->request->body('name'));
        $slug = (string) $this->slug($name);

        $duplicate = $categories->findBySlug($slug);

        if ($duplicate && (int) $duplicate['id'] !== $id) {
            return $this->response->json([
                'message' => 'Os dados informados são inválidos.',
                'errors'  => ['slug' => ['Já existe uma categoria com este identificador.']],
            ], 422);
        }

        $category = $categories->update($id, [
            'name'        => $name,
            'slug'        => $slug,
            'description' => $this->description($category['description']),
            'status'      => $this->status((int) $category['status']),
        ]);

        (new Logger('category'))->info('Categoria atualizada: {name}', [
            'category_id' => $id,
            'name'        => $category['name'],
        ]);

        return $this->response->json([
            'message'  => 'Categoria atualizada com sucesso.',
            'category' => $this->present($category),
        ]);
    }

    /**
     * DELETE /api/categories/{id}
     * Remove uma categoria.
     */
    public function destroy(PDO $db, int $id)
    {
        $this->authorize();

        $categories = new Category($db);
        $category   = $categories->findById($id);

        if (!$category) {
            return $this->response->json(['message' => 'Categoria não encontrada.'], 404);
        }

        $categories->delete($id);

        (new Logger('category'))->info('Categoria removida: {name}', [
            'category_id' => $id,
            'name'        => $category['name'],
        ]);

        return $this->response->json(['message' => 'Categoria removida com sucesso.']);
    }

    /**
     * Restringe o recurso ao usuário autenticado com papel de administrador.
     */
    private function authorize(): void
    {
        $guard = new Guard();

        if (!$guard->allowsPanel($guard->user())) {
            $this->response->json(['message' => 'Não autorizado.'], 401);
        }
    }

    /**
     * Gera o slug a partir do valor informado ou, na ausência, do nome.
     */
    private function slug(string $name): string
    {
        $slug = trim((string) $this->request->body('slug'));

        return $this->pkg->slug($slug !== '' ? $slug : $name);
    }

    /**
     * Normaliza a descrição (opcional). Quando ausente, mantém o valor anterior.
     */
    private function description(?string $fallback = null): ?string
    {
        $description = $this->request->body('description');

        if ($description === null) {
            return $fallback;
        }

        $description = trim((string) $description);

        return $description === '' ? null : $description;
    }

    /**
     * Normaliza o status (0/1). Quando ausente, mantém o valor anterior.
     */
    private function status(?int $fallback = null): int
    {
        $status = $this->request->body('status');

        if ($status === null) {
            return $fallback ?? 1;
        }

        return in_array($status, [1, '1', true, 'true'], true) ? 1 : 0;
    }

    /**
     * Converte os tipos das colunas numéricas antes de expor na resposta.
     */
    private function present(array $category): array
    {
        $category['id']     = (int) $category['id'];
        $category['status'] = (int) $category['status'];

        return $category;
    }
}
