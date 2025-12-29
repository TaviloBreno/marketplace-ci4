<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Rotas protegidas - requer autenticação
$routes->group('', ['filter' => 'session'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    
    // Rotas do Organizador
    $routes->group('organizer', function ($routes) {
        // Rotas que não precisam de conta ativa
        $routes->get('become', 'Organizer::become');
        $routes->post('register', 'Organizer::register', ['as' => 'organizer.register']);
        $routes->get('account-status', 'Organizer::accountStatus');
        $routes->get('onboarding-complete', 'Organizer::onboardingComplete');
        $routes->get('onboarding-refresh', 'Organizer::onboardingRefresh');
        
        // Rotas que precisam de conta ativa
        $routes->group('', ['filter' => 'organizer'], function ($routes) {
            $routes->get('dashboard', 'Organizer::dashboard');
            $routes->get('stripe-dashboard', 'Organizer::stripeDashboard');
            
            // Eventos
            $routes->get('events', 'Event::index');
            $routes->get('events/create', 'Event::create');
            $routes->post('events/store', 'Event::store');
            $routes->get('events/(:num)', 'Event::show/$1');
            $routes->get('events/(:num)/edit', 'Event::edit/$1');
            $routes->post('events/(:num)/update', 'Event::update/$1');
            $routes->post('events/(:num)/publish', 'Event::publish/$1');
            $routes->post('events/(:num)/cancel', 'Event::cancel/$1');
            $routes->get('events/(:num)/seat-map', 'Event::seatMap/$1');
            $routes->get('events/(:num)/layout', 'Event::getLayout/$1');
        });
    });
});

service('auth')->routes($routes);
