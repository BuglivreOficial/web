<?php
namespace Core\Auth;

use Exception;

/**
 * Implementação mínima de JWT (JSON Web Token) com assinatura HS256.
 * Sem dependências externas — usa apenas hash_hmac do próprio PHP.
 */
class Jwt
{
    private const ALGORITHM = 'HS256';

    private string $secret;
    private int $ttl;

    public function __construct(?string $secret = null, ?int $ttl = null)
    {
        $this->secret = $secret ?? (string) ($_ENV['JWT_SECRET'] ?? '');
        $this->ttl = $ttl ?? (int) ($_ENV['JWT_TTL'] ?? 3600);

        if ($this->secret === '') {
            throw new Exception('A variável de ambiente JWT_SECRET não foi definida.');
        }
    }

    /**
     * Gera um token assinado a partir das claims informadas.
     * As claims "iat" e "exp" são adicionadas automaticamente.
     */
    public function encode(array $claims): string
    {
        $now = time();

        $payload = array_merge([
            'iat' => $now,
            'exp' => $now + $this->ttl,
        ], $claims);

        $segments = [
            $this->base64UrlEncode(json_encode(['alg' => self::ALGORITHM, 'typ' => 'JWT'])),
            $this->base64UrlEncode(json_encode($payload)),
        ];

        $segments[] = $this->sign(implode('.', $segments));

        return implode('.', $segments);
    }

    /**
     * Valida a assinatura/expiração e devolve as claims do token.
     * Lança exceção quando o token é inválido ou expirado.
     */
    public function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception('Token malformado.');
        }

        [$header, $payload, $signature] = $parts;

        if (!hash_equals($this->sign($header . '.' . $payload), $signature)) {
            throw new Exception('Assinatura do token inválida.');
        }

        $claims = json_decode($this->base64UrlDecode($payload), true);

        if (!is_array($claims)) {
            throw new Exception('Conteúdo do token inválido.');
        }

        if (isset($claims['exp']) && time() >= (int) $claims['exp']) {
            throw new Exception('Token expirado.');
        }

        return $claims;
    }

    /**
     * Extrai o token do cabeçalho Authorization: Bearer <token>.
     */
    public function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function sign(string $data): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $data, $this->secret, true));
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}
