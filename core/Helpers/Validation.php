<?php
namespace Core\Helpers;

use Core\Http\Request;

class Validation
{
    private Request $request;
    private array $erros = [];

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Aplica as regras (ex.: 'required|min:3|email') e devolve true quando
     * existem erros de validação.
     */
    public function rules(array $rules): bool
    {
        foreach ($rules as $key => $value) {
            foreach (explode('|', $value) as $rule) {
                $param = null;

                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }

                $data = $this->request->body($key);

                if ($rule === 'required') {
                    if (!$this->required($data)) {
                        $this->erros[$key][] = 'O campo é obrigatório.';
                        break;
                    }
                    continue;
                }

                // As demais regras só se aplicam a valores preenchidos.
                if ($data === null || $data === '') {
                    continue;
                }

                if ($rule === 'min' && !$this->min($data, (int) $param)) {
                    $this->erros[$key][] = "Mínimo {$param} caracteres.";
                }

                if ($rule === 'email' && !$this->email($data)) {
                    $this->erros[$key][] = 'Informe um e-mail válido.';
                }
            }
        }

        return $this->erros !== [];
    }

    public function getErros(): array
    {
        return $this->erros;
    }

    public function required(mixed $value): bool
    {
        return is_scalar($value) && trim((string) $value) !== '';
    }

    public function min(mixed $value, int $size): bool
    {
        return is_scalar($value) && strlen(trim((string) $value)) >= $size;
    }

    public function email(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
