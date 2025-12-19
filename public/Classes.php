<?php

return array (
  'GET' => 
  array (
    '/users' => 
    array (
      'controller' => 'App\\Controller\\UserController',
      'action' => 'index',
    ),
  ),
  'POST' => 
  array (
    '/users' => 
    array (
      'controller' => 'App\\Controller\\UserController',
      'action' => 'store',
    ),
  ),
);
