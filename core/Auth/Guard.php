<?php
namespace Core\Auth;

use Throwable;

/**
 * Controla a autenticação do painel administrativo a partir do token JWT
 * (gerado pela API de login) armazenado em um cookie HttpOnly.
 */
class Guard
{
    public const COOKIE = 'manga_admin_token';

    /** Papéis autorizados a acessar o painel administrativo. */
    public const PANEL_ROLES = ['admin'];

    /** Validade (em segundos) do token quando "lembrar-me" é marcado. */
    public const REMEMBER_TTL = 60 * 60 * 24 * 30; // 30 dias

    /**
     * Grava o token no cookie de autenticação do painel.
     */
    public function login(string $token, bool $remember = false): void
    {
        $options = [
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => $this->isSecure(),
        ];

        if ($remember) {
            $options['expires'] = time() + self::REMEMBER_TTL;
        }

        setcookie(self::COOKIE, $token, $options);
        $_COOKIE[self::COOKIE] = $token;
    }

    /**
     * Remove o cookie de autenticação.
     */
    public function logout(): void
    {
        unset($_COOKIE[self::COOKIE]);

        setcookie(self::COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => $this->isSecure(),
        ]);
    }

    /**
     * Indica se o contexto atual é HTTPS (necessário para a flag Secure).
     */
    private function isSecure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        return (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }

    /**
     * Verifica se o usuário (claims do token) pode acessar o painel.
     */
    public function allowsPanel(?array $user): bool
    {
        return $user !== null
            && in_array((string) ($user['role'] ?? ''), self::PANEL_ROLES, true);
    }

    /**
     * Devolve as claims do usuário autenticado, ou null quando não há sessão válida.
     */
    public function user(): ?array
    {
        $token = $_COOKIE[self::COOKIE] ?? null;

        if (!is_string($token) || $token === '') {
            return null;
        }

        try {
            return (new Jwt())->decode($token);
        } catch (Throwable $e) {
            return null;
        }
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }
}
