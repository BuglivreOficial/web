<?php
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\User;
use Core\Auth\Guard;
use Core\Auth\Jwt;
use Core\Log\Logger;
use PDO;

class AuthController extends BaseController
{
    /**
     * POST /api/auth/register
     * Cria um novo usuário e devolve o token de acesso.
     */
    public function register(PDO $db)
    {
        if ($this->validation->rules([
            'name'     => 'required|min:3',
            'username' => 'required|min:3',
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ])) {
            return $this->response->json([
                'message' => 'Os dados informados são inválidos.',
                'errors'  => $this->validation->getErros(),
            ], 422);
        }

        $name     = trim((string) $this->request->body('name'));
        $username = trim((string) $this->request->body('username'));
        $email    = trim((string) $this->request->body('email'));
        $password = (string) $this->request->body('password');

        $users = new User($db);

        if ($users->findByUsername($username)) {
            return $this->response->json([
                'message' => 'Os dados informados são inválidos.',
                'errors'  => ['username' => ['Este nome de usuário já está em uso.']],
            ], 422);
        }

        if ($users->findByEmail($email)) {
            return $this->response->json([
                'message' => 'Os dados informados são inválidos.',
                'errors'  => ['email' => ['Este e-mail já está cadastrado.']],
            ], 422);
        }

        $user = $users->create([
            'name'     => $name,
            'username' => $username,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return $this->response->json([
            'message' => 'Usuário registrado com sucesso.',
            'user'    => $this->present($user),
            'token'   => $this->token($user),
        ], 201);
    }

    /**
     * POST /api/auth/login
     * Autentica o usuário por nome de usuário e senha.
     */
    public function login(PDO $db)
    {
        if ($this->validation->rules([
            'username' => 'required',
            'password' => 'required',
        ])) {
            return $this->response->json([
                'message' => 'Os dados informados são inválidos.',
                'errors'  => $this->validation->getErros(),
            ], 422);
        }

        $username = trim((string) $this->request->body('username'));
        $password = (string) $this->request->body('password');

        $users = new User($db);
        $user  = $users->findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            (new Logger('auth'))->warning('Tentativa de login inválida: {username}', [
                'username' => $username,
                'ip'       => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);

            return $this->response->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        if ((int) $user['status'] !== 1) {
            (new Logger('auth'))->warning('Login bloqueado (conta desativada): {username}', [
                'user_id'  => (int) $user['id'],
                'username' => $user['username'],
            ]);

            return $this->response->json([
                'message' => 'Esta conta está desativada.',
            ], 403);
        }

        $users->updateLastLogin((int) $user['id']);

        $remember = (bool) $this->request->body('remember');
        $token    = $this->token($user, $remember);

        $guard = new Guard();
        $panel = $guard->allowsPanel($user);

        // Só integra o login ao painel (cookie HttpOnly) quando o papel do
        // usuário é autorizado. Usuários comuns recebem apenas o token da API.
        if ($panel) {
            $guard->login($token, $remember);
        }

        (new Logger('auth'))->info('Login realizado: {username}', [
            'user_id'    => (int) $user['id'],
            'username'   => $user['username'],
            'role'       => $user['role'],
            'painel'     => $panel,
            'remember'   => $remember,
        ]);

        return $this->response->json([
            'message' => 'Login realizado com sucesso.',
            'panel'   => $panel,
            'user'    => $this->present($user),
            'token'   => $token,
        ]);
    }

    /**
     * Remove dados sensíveis antes de expor o usuário na resposta.
     */
    private function present(array $user): array
    {
        unset($user['password'], $user['remember_token']);
        $user['id'] = (int) $user['id'];
        $user['status'] = (int) $user['status'];

        return $user;
    }

    private function token(array $user, bool $remember = false): string
    {
        $ttl = $remember ? Guard::REMEMBER_TTL : null;

        return (new Jwt(null, $ttl))->encode([
            'sub'      => (int) $user['id'],
            'name'     => $user['name'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ]);
    }
}
