<?php
namespace Core\Helper;

class Helper  {
    public static function url(string $url = '') {
        return $_ENV['APP_URL'] . $url;
    }
}