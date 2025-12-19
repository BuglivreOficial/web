<?php
namespace App\Controller;

use Core\Attributes\Route;

class UserController
{
  #[Route('/users', 'GET')]
  public function index()
  {
    echo 'Lista de usuários';
    echo "Uso atual: " . (memory_get_usage() / 1024 / 1024) . " MB\n";
    echo "Pico de uso: " . (memory_get_peak_usage() / 1024 / 1024) . " MB\n";
    $tempo = microtime(true) - APP_START;
    echo round($tempo * 1000, 2) . 'ms';
    //header('X-Response-Time: ' . round($tempo * 1000, 2) . 'ms');
  }

  #[Route('/users', 'POST')]
  public function store()
  {
    echo 'Criar usuário';
  }
}