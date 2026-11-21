<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Core\Exceptions\MysqlException;
use Core\Helpers\Slave;
use Core\Http\HttpException;
use Core\Http\Response;
use Core\Log\Logger;

define('DIR', dirname(__DIR__));

$response = new Response();

try {

    $slave = new Slave();

    if ($slave->inProduction()) {
        $slave->envProduction();
    } else {
        $slave->envDevelopment();
    }

    $route = new \Core\Http\Routing();

    $db = new \Core\Database\Mysql()->make();

    require DIR . '/routes/web.php';
    $route->group('/api', function ($route) {
        require DIR . '/routes/api.php';
    });

    $route->start($db);
} catch (HttpException $e) {
    (new Logger('http'))->warning('Requisição rejeitada: {message}', [
        'message' => $e->getMessage(),
        'status'  => $e->getCode(),
        'method'  => $_SERVER['REQUEST_METHOD'] ?? '',
        'path'    => $_SERVER['REQUEST_URI'] ?? '',
    ]);

    $response->fail($e->getCode(), $e->getMessage());
} catch (MysqlException $e) {
    (new Logger('database'))->critical('Falha na conexão com o banco de dados.', [
        'error' => $e->getMessage(),
    ]);

    $response->fail(500, 'Não foi possível conectar ao banco de dados.');
} catch (Throwable $e) {
    (new Logger('app'))->error('Erro não tratado: {message}', [
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);

    $response->fail(500);
}
