<?php
namespace App\Controller;

use Core\Attributes\Route;

class TesteController
{
  #[Route('/teste', 'GET')]
  public function index() {
    echo 'Lista de usuários';
    $tempo = microtime(true) - APP_START;
    echo round($tempo * 1000, 2) . 'ms';
    //header('X-Response-Time: ' . round($tempo * 1000, 2) . 'ms');
  }

  #[Route('/teste', 'POST')]
  public function store() {
    echo 'Criar usuário';
  }
}