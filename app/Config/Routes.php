<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ============================================
// ROTAS PÚBLICAS - Área do Cliente
// ============================================

// Home e Listagem de Eventos
$routes->get('/', 'PublicController::index');
$routes->get('events', 'PublicController::events');
$routes->get('events/(:segment)', 'PublicController::event/$1');
$routes->get('events/(:segment)/seats/(:num)', 'PublicController::selectSeats/$1/$2');
$routes->post('events/seats-status', 'PublicController::getSeatsStatus');

// Carrinho (algumas rotas precisam de autenticação)
$routes->get('cart', 'CartController::index');
$routes->post('cart/add', 'CartController::add');
$routes->post('cart/remove', 'CartController::remove');
$routes->post('cart/clear', 'CartController::clear');
$routes->get('cart/count', 'CartController::getCount');

// Webhook do Stripe (não requer autenticação)
$routes->post('checkout/webhook', 'CheckoutController::webhook');

// Validação de ingresso via QR Code (público)
$routes->get('tickets/validate/(:segment)', 'TicketController::validateQR/$1');

// ============================================
// ROTAS PROTEGIDAS - Requer Autenticação
// ============================================
$routes->group('', ['filter' => 'session'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    
    // Checkout
    $routes->get('checkout', 'CheckoutController::index');
    $routes->post('checkout/process', 'CheckoutController::process');
    $routes->get('checkout/success', 'CheckoutController::success');
    $routes->get('checkout/cancel', 'CheckoutController::cancel');
    
    // Pedidos do usuário
    $routes->get('orders', 'OrderController::index');
    $routes->get('orders/(:num)', 'OrderController::show/$1');
    $routes->get('orders/(:num)/refund', 'OrderController::requestRefund/$1');
    $routes->post('orders/(:num)/refund', 'OrderController::processRefund/$1');
    $routes->get('orders/(:num)/download-tickets', 'OrderController::downloadTickets/$1');
    
    // Ingressos do usuário
    $routes->get('tickets', 'TicketController::index');
    $routes->get('tickets/(:segment)', 'TicketController::show/$1');
    $routes->get('tickets/(:segment)/print', 'TicketController::print/$1');
    $routes->get('tickets/(:segment)/download', 'TicketController::download/$1');
    
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
