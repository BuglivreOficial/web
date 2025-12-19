<?php
namespace Core\Router;

use ReflectionMethod;
use ReflectionClass;
use Core\Attributes\Route;

class Router
{
  private array $controllers = [];
  private array $routes = [];

  public function start()
  {
    $diretorio = __DIR__ . '/../../app/Controller';

    $handle = opendir($diretorio); // Abre o diretório

    if ($handle) {
      while (false !== ($entrada = readdir($handle))) { // Lê uma entrada por vez
        if ($entrada != "." && $entrada != "..") { // Ignora '.' e '..'
          $this->controllers[] = $entrada;
        }
      }
      closedir($handle); // Fecha o manipulador

      $code = "<?php\n\nreturn " . var_export($this->controllers, true) . ";\n";
      file_put_contents(
        '../storage/cache/controllers.php',
        $code,
        LOCK_EX
      );
    } else {
      echo "Não foi possível abrir o diretório!";
    }

    $this->registerControllers();
  }

  public function registerControllers(): void
  {

    foreach ($this->controllers as $controller) {
      $rc = new ReflectionClass('App\\Controller\\' . substr($controller, 0, -4));

      foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        foreach ($method->getAttributes(Route::class) as $attr) {
          $route = $attr->newInstance();
          $this->routes[] = [
            'path' => $route->path,
            'method' => strtoupper($route->method),
            'controller' => 'App\\Controller\\' . substr($controller, 0, -4),
            'action' => $method->getName()
          ];
        }
      }
    }

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    $this->dispatch($uri, $method);
  }

  public function dispatch(string $uri, string $httpMethod): void
  {
    dump($this->routes);
    $compiled = [];

    foreach ($this->routes as $route) {
      $compiled[$route['method']][$route['path']] = [
        'controller' => $route['controller'],
        'action' => $route['action'],
      ];
    }


    $code = "<?php\n\nreturn " . var_export($compiled, true) . ";\n";

    file_put_contents(
      '../storage/cache/routes.php',
      $code,
      LOCK_EX
    );
    foreach ($this->routes as $route) {
      if ($route['path'] === $uri && $route['method'] === $httpMethod) {
        $controller = new $route['controller'];
        $action = $route['action'];
        /////////
        $tempo = microtime(true) - APP_START;
        echo round($tempo * 1000, 2) . 'ms';
        ////////
        $controller->$action();
        return;
      }
    }

    http_response_code(404);
    echo '404';
  }
}