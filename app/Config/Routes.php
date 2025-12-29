<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Rotas protegidas - requer autenticação
$routes->group('', ['filter' => 'session'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
});

service('auth')->routes($routes);
