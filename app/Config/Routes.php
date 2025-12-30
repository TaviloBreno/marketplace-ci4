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
$routes->get('eventos', 'PublicController::events');
$routes->get('evento/(:segment)', 'PublicController::event/$1');
$routes->get('evento/(:segment)/assentos/(:num)', 'PublicController::selectSeats/$1/$2');
$routes->post('eventos/seats-status', 'PublicController::getSeatsStatus');
$routes->get('busca', 'PublicController::search');

// Carrinho
$routes->get('carrinho', 'CartController::index');
$routes->post('carrinho/adicionar', 'CartController::add');
$routes->post('carrinho/remover', 'CartController::remove');
$routes->post('carrinho/limpar', 'CartController::clear');
$routes->get('carrinho/contador', 'CartController::getCount');

// Webhook do Stripe (não requer autenticação)
$routes->post('checkout/webhook', 'CheckoutController::webhook');

// Validação de ingresso via QR Code (público)
$routes->get('ingresso/validar/(:segment)', 'TicketController::validateQR/$1');

// ============================================
// ROTAS PROTEGIDAS - Requer Autenticação
// ============================================
$routes->group('', ['filter' => 'session'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    
    // Checkout
    $routes->get('checkout', 'CheckoutController::index');
    $routes->post('checkout/processar', 'CheckoutController::process');
    $routes->get('checkout/sucesso', 'CheckoutController::success');
    $routes->get('checkout/cancelar', 'CheckoutController::cancel');
    
    // Pedidos do usuário
    $routes->get('meus-pedidos', 'OrderController::index');
    $routes->get('pedido/(:num)', 'OrderController::show/$1');
    $routes->get('pedido/(:num)/confirmacao', 'CheckoutController::confirmation/$1');
    $routes->get('pedido/(:num)/reembolso', 'OrderController::requestRefund/$1');
    $routes->post('pedido/(:num)/reembolso', 'OrderController::processRefund/$1');
    $routes->get('pedido/(:num)/baixar-ingressos', 'OrderController::downloadTickets/$1');
    
    // Ingressos do usuário
    $routes->get('meus-ingressos', 'TicketController::index');
    $routes->get('ingresso/(:segment)', 'TicketController::show/$1');
    $routes->get('ingresso/(:segment)/imprimir', 'TicketController::print/$1');
    $routes->get('ingresso/(:segment)/baixar', 'TicketController::download/$1');
    
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
