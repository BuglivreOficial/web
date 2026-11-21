<?php
namespace Core\Http;

use Exception;

/**
 * Exceção HTTP: carrega o status que deve ser devolvido ao cliente
 * (ex.: 404, 405). O código da exceção é o próprio status HTTP.
 */
class HttpException extends Exception
{
    public function __construct(int $status, string $message = '')
    {
        parent::__construct($message !== '' ? $message : self::defaultMessage($status), $status);
    }

    private static function defaultMessage(int $status): string
    {
        return match ($status) {
            400 => 'Requisição inválida.',
            401 => 'Não autenticado.',
            403 => 'Acesso negado.',
            404 => 'Página não encontrada.',
            405 => 'Método não permitido.',
            default => 'Erro na requisição.',
        };
    }
}
