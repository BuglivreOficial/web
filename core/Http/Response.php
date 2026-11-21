<?php
namespace Core\Http;

class Response
{
    public function json(array $dados, int $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($dados);
        exit;
    }

    public function redirect(string $url, int $status = 302) {
        header('Location: ' . $url, true, $status);
        exit;
    }

    public function view(string $view, array $data = [], int $status = 200) {
        $loader = new \Twig\Loader\FilesystemLoader(DIR . '/layout');
        $twig = new \Twig\Environment($loader, [
            'debug' => false,
        ]);

        http_response_code($status);
        echo $twig->render($view . '.html', $data);
        exit;
    }

    /**
     * Renderiza a página de erro quando a resposta for HTML. Em requisições
     * de API devolve JSON, sempre com o status HTTP informado.
     */
    public function fail(int $status, string $message = '') {
        if ($this->wantsJson()) {
            $this->json(['message' => $message ?: 'Erro na requisição.'], $status);
        }

        $template = 'error/' . $status;
        if (!is_file(DIR . '/layout/' . $template . '.html')) {
            $template = 'error/500';
        }

        $this->view($template, ['message' => $message], $status);
    }

    /**
     * Indica se o cliente espera JSON (rotas /api ou cabeçalho Accept).
     */
    public function wantsJson(): bool
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        if (str_starts_with($path, '/api/') || $path === '/api') {
            return true;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        return str_contains($accept, 'application/json');
    }
}