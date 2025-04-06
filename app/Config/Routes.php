<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', function($routes) {
    $routes->post('points', 'Api::registerPoint');
    $routes->get('points', 'Api::listPoints');
    $routes->post('collections', 'Api::registerCollection');
});