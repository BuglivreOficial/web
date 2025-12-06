<?php
namespace Core\Http;

use Core\Helper\Helper;

class Response {
  public static function render(string $path, array $data) {
    $loader = new \Twig\Loader\FilesystemLoader('./../template/');
    if ($_ENV['PRODUCTION'] === 'true') {
      $twig = new \Twig\Environment($loader, [
        'cache' => './../template/cache',
      ]);
    } else {
      $twig = new \Twig\Environment($loader, [
        'cache' => false,
      ]);
    }
    $twig->addFunction(new \Twig\TwigFunction('url', function (string $url = '') use ($data) {
      return Helper::url($url);
    }));
    echo $twig->render($path, $data);
  }
}