<?php
namespace App\Models;

use PDO;

/**
 * Model responsável pela persistência de categorias.
 * Isola o acesso à tabela `categories` do restante da aplicação.
 */
class Category
{
    private const TABLE = 'categories';

    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Lista todas as categorias em ordem alfabética.
     */
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM ' . self::TABLE . ' ORDER BY name ASC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . self::TABLE . ' WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . self::TABLE . ' WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Persiste uma nova categoria e devolve o registro criado.
     */
    public function create(array $data): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . ' (name, slug, description, status)
             VALUES (:name, :slug, :description, :status)'
        );

        $stmt->execute([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 1,
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    /**
     * Atualiza uma categoria existente e devolve o registro atualizado.
     */
    public function update(int $id, array $data): ?array
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . '
             SET name = :name, slug = :slug, description = :description, status = :status
             WHERE id = :id'
        );

        $stmt->execute([
            'id'          => $id,
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 1,
        ]);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
