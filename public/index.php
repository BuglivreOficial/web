<?php

require __DIR__ . '/../vendor/autoload.php';

/**
 * 
 */
use Dotenv\Exception\InvalidPathException;

try {
    $dotenv = Dotenv\Dotenv::createImmutable('..');
    $dotenv->load();
} catch (InvalidPathException $e) {
    dump($e->getMessage());
}


/**
 * 
 */
use Pecee\SimpleRouter\SimpleRouter as Router;
require("../router/api.php");
require("../router/web.php");

Router::start();