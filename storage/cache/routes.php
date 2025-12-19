<?php

return array (
  'GET' => 
  array (
    '/teste' => 
    array (
      'controller' => 'App\\Controller\\TesteController',
      'action' => 'index',
    ),
    '/users' => 
    array (
      'controller' => 'App\\Controller\\UserController',
      'action' => 'index',
    ),
  ),
  'POST' => 
  array (
    '/teste' => 
    array (
      'controller' => 'App\\Controller\\TesteController',
      'action' => 'store',
    ),
    '/users' => 
    array (
      'controller' => 'App\\Controller\\UserController',
      'action' => 'store',
    ),
  ),
);
