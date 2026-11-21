<?php
namespace App\Models;

use PDO;

/**
 * Model responsável pela persistência de usuários.
 * Isola o acesso à tabela `users` do restante da aplicação.
 */
class User
{
    private const TABLE = 'users';

    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . self::TABLE . ' WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . self::TABLE . ' WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . self::TABLE . ' WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Persiste um novo usuário e devolve o registro criado.
     */
    public function create(array $data): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . ' (name, username, email, password, role, status)
             VALUES (:name, :username, :email, :password, :role, :status)'
        );

        $stmt->execute([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => $data['role'] ?? 'user',
            'status'   => $data['status'] ?? 1,
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE ' . self::TABLE . ' SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
