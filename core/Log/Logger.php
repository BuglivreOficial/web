<?php
namespace Core\Log;

/**
 * Sistema de logs da aplicação.
 *
 * Grava linhas no formato:
 *   [2026-11-20 23:00:00] auth.INFO: Login realizado. {"user_id":1}
 *
 * Cada canal gera um arquivo diário em storage/logs/{canal}-{Y-m-d}.log,
 * sem dependências externas.
 */
class Logger
{
    public const EMERGENCY = 'emergency';
    public const ALERT     = 'alert';
    public const CRITICAL  = 'critical';
    public const ERROR     = 'error';
    public const WARNING   = 'warning';
    public const NOTICE    = 'notice';
    public const INFO      = 'info';
    public const DEBUG     = 'debug';

    private string $channel;

    public function __construct(string $channel = 'app')
    {
        $this->channel = preg_replace('/[^a-z0-9_-]/i', '-', $channel) ?: 'app';
    }

    public function emergency(string $message, array $context = []): void { $this->log(self::EMERGENCY, $message, $context); }
    public function alert(string $message, array $context = []): void     { $this->log(self::ALERT, $message, $context); }
    public function critical(string $message, array $context = []): void  { $this->log(self::CRITICAL, $message, $context); }
    public function error(string $message, array $context = []): void     { $this->log(self::ERROR, $message, $context); }
    public function warning(string $message, array $context = []): void   { $this->log(self::WARNING, $message, $context); }
    public function notice(string $message, array $context = []): void    { $this->log(self::NOTICE, $message, $context); }
    public function info(string $message, array $context = []): void      { $this->log(self::INFO, $message, $context); }
    public function debug(string $message, array $context = []): void     { $this->log(self::DEBUG, $message, $context); }

    public function log(string $level, string $message, array $context = []): void
    {
        $line = sprintf(
            '[%s] %s.%s: %s%s',
            date('Y-m-d H:i:s'),
            $this->channel,
            strtoupper($level),
            $this->interpolate($message, $context),
            $context === [] ? '' : ' ' . $this->encode($context)
        );

        $this->write($line);
    }

    private function write(string $line): void
    {
        $dir = $this->directory();

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }

        @file_put_contents($this->file($dir), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function directory(): string
    {
        $base = defined('DIR') ? DIR : dirname(__DIR__, 2);

        return $base . '/storage/logs';
    }

    private function file(string $dir): string
    {
        return $dir . '/' . $this->channel . '-' . date('Y-m-d') . '.log';
    }

    /**
     * Substitui marcadores {chave} pelos valores do contexto (padrão PSR-3).
     */
    private function interpolate(string $message, array $context): string
    {
        if ($context === []) {
            return $message;
        }

        $replace = [];
        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $replace['{' . $key . '}'] = (string) $value;
            }
        }

        return strtr($message, $replace);
    }

    private function encode(array $context): string
    {
        return json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
