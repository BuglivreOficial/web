<?php
define('APP_START', microtime(true));
echo "Uso atual: " . (memory_get_usage() / 1024 / 1024) . " MB\n";
// public/index.php

require '../vendor/autoload.php';

use Core\Router\Router;

(New Router)->start();