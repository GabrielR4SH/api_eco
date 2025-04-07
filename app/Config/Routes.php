<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Grupo de API com autenticação
$routes->group('api', function($routes) {
    // Rotas públicas
    $routes->post('auth/register', 'AuthController::register');
    $routes->post('auth/login', 'AuthController::login');
    
    // Rotas que exigem autenticação
    $routes->group('', ['filter' => 'jwt'], function($routes) {
        // Pontos de coleta
        $routes->post('points', 'Api::registerPoint');
        $routes->get('points', 'Api::listPoints');
        $routes->get('points/nearby', 'Api::nearbyPoints');
        $routes->get('points/(:num)', 'Api::getPoint/$1');
        $routes->put('points/(:num)', 'Api::updatePoint/$1');
        $routes->delete('points/(:num)', 'Api::deletePoint/$1');
        
        // Coletas
        $routes->post('collections', 'Api::registerCollection');
        $routes->get('collections', 'Api::listCollections');
        $routes->get('collections/(:num)', 'Api::getCollection/$1');
        
        // Relatórios
        $routes->get('reports/impact', 'ReportController::environmentalImpact');
        $routes->get('reports/partners', 'ReportController::partnerActivity');
        
        // Usuários
        $routes->get('users/profile', 'UserController::profile');
        $routes->put('users/profile', 'UserController::updateProfile');
        
        // Gamificação
        $routes->post('disposals', 'DisposalController::register');
        $routes->get('leaderboard', 'GameController::leaderboard');
    });
    
    // Pontos de coleta públicos (sem autenticação)
    $routes->get('public/points/nearby', 'Api::publicNearbyPoints');
});