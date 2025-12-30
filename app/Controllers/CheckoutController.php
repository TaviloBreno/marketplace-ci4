<?php

namespace App\Controllers;

use App\Services\PaymentService;
use App\Models\EventModel;
use App\Models\EventDayModel;
use App\Models\SectorModel;
use App\Models\SeatModel;
use App\Models\SeatBookingModel;
use CodeIgniter\HTTP\ResponseInterface;

class CheckoutController extends BaseController
{
    protected PaymentService $paymentService;
    protected const SESSION_KEY = 'cart_items';

    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }

    /**
     * Página de checkout
     */
    public function index()
    {
        // Verificar se está logado
        if (!auth()->loggedIn()) {
            return redirect()->to('login?redirect=' . urlencode(current_url()));
        }
        
        // Verificar carrinho
        $cartItems = $this->getCartItems();
        
        if (empty($cartItems)) {
            return redirect()->to('carrinho')->with('error', 'Seu carrinho está vazio.');
        }
        
        // Limpar itens expirados
        $this->cleanupExpiredItems();
        $cartItems = $this->getCartItems();
        
        if (empty($cartItems)) {
            return redirect()->to('carrinho')->with('error', 'Todos os itens do carrinho expiraram.');
        }
        
        // Obter detalhes dos itens
        $itemDetails = $this->getCartDetails($cartItems);
        
        // Calcular totais
        $totals = $this->paymentService->calculateTotals($cartItems);
        
        // Criar PaymentIntent
        $paymentIntent = $this->paymentService->createPaymentIntent($cartItems, auth()->id());
        
        if (!$paymentIntent['success']) {
            return redirect()->to('carrinho')
                ->with('error', 'Erro ao iniciar pagamento: ' . ($paymentIntent['error'] ?? 'Erro desconhecido'));
        }
        
        // Salvar payment intent na sessão
        session()->set('payment_intent_id', $paymentIntent['payment_intent_id']);
        
        return view('public/checkout/index', [
            'items' => $itemDetails,
            'totals' => $totals,
            'stripePublicKey' => $this->paymentService->getPublicKey(),
            'clientSecret' => $paymentIntent['client_secret'],
            'user' => auth()->user()
        ]);
    }

    /**
     * Processar pagamento (webhook ou confirmação client-side)
     */
    public function process(): ResponseInterface
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Usuário não autenticado'
            ])->setStatusCode(401);
        }
        
        $json = $this->request->getJSON(true);
        $paymentIntentId = $json['payment_intent_id'] ?? session()->get('payment_intent_id');
        
        if (!$paymentIntentId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Payment intent não encontrado'
            ])->setStatusCode(400);
        }
        
        $cartItems = $this->getCartItems();
        
        if (empty($cartItems)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Carrinho vazio'
            ])->setStatusCode(400);
        }
        
        // Confirmar pagamento e criar pedido
        $result = $this->paymentService->confirmPayment(
            $paymentIntentId,
            $cartItems,
            auth()->id()
        );
        
        if ($result['success']) {
            // Limpar carrinho e sessão
            $this->clearCart();
            session()->remove('payment_intent_id');
            
            return $this->response->setJSON([
                'success' => true,
                'order_id' => $result['order_id'],
                'redirect_url' => base_url('pedido/' . $result['order_id'] . '/confirmacao')
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'error' => $result['error'] ?? 'Erro ao processar pagamento'
        ])->setStatusCode(400);
    }

    /**
     * Página de confirmação do pedido
     */
    public function confirmation(int $orderId)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }
        
        $orderModel = new \App\Models\OrderModel();
        $order = $orderModel->where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();
        
        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Obter tickets
        $ticketModel = new \App\Models\TicketModel();
        $tickets = $ticketModel->where('order_id', $orderId)->findAll();
        
        // Obter detalhes do evento
        $eventModel = new EventModel();
        $event = $eventModel->find($order->event_id);
        
        // Obter dia do evento (do primeiro ticket)
        $eventDay = null;
        if (!empty($tickets)) {
            $eventDayModel = new EventDayModel();
            $eventDay = $eventDayModel->find($tickets[0]->event_day_id);
        }
        
        // Detalhes dos tickets
        $ticketDetails = [];
        $seatModel = new SeatModel();
        $sectorModel = new SectorModel();
        
        foreach ($tickets as $ticket) {
            $seat = $seatModel->find($ticket->seat_id);
            $queue = null;
            $sector = null;
            
            if ($seat) {
                $queue = model('QueueModel')->find($seat->queue_id);
                if ($queue) {
                    $sector = $sectorModel->find($queue->sector_id);
                }
            }
            
            $ticketDetails[] = [
                'ticket' => $ticket,
                'seat' => $seat,
                'queue' => $queue,
                'sector' => $sector
            ];
        }
        
        return view('public/checkout/confirmation', [
            'order' => $order,
            'event' => $event,
            'eventDay' => $eventDay,
            'tickets' => $ticketDetails
        ]);
    }

    /**
     * Obter itens do carrinho
     */
    protected function getCartItems(): array
    {
        return session()->get(self::SESSION_KEY) ?? [];
    }

    /**
     * Limpar carrinho
     */
    protected function clearCart(): void
    {
        session()->remove(self::SESSION_KEY);
    }

    /**
     * Limpar itens expirados
     */
    protected function cleanupExpiredItems(): void
    {
        $cartItems = $this->getCartItems();
        $now = date('Y-m-d H:i:s');
        $validItems = [];
        $seatBookingModel = new SeatBookingModel();
        
        foreach ($cartItems as $item) {
            if ($item['expires_at'] <= $now) {
                $seatBookingModel->update($item['booking_id'], [
                    'status' => 'cancelled',
                    'updated_at' => $now
                ]);
                continue;
            }
            
            $booking = $seatBookingModel->find($item['booking_id']);
            if (!$booking || $booking->status !== 'reserved') {
                continue;
            }
            
            $validItems[] = $item;
        }
        
        session()->set(self::SESSION_KEY, $validItems);
    }

    /**
     * Obter detalhes dos itens
     */
    protected function getCartDetails(array $cartItems): array
    {
        $details = [];
        $eventModel = new EventModel();
        $eventDayModel = new EventDayModel();
        $seatModel = new SeatModel();
        $sectorModel = new SectorModel();
        
        foreach ($cartItems as $item) {
            $event = $eventModel->find($item['event_id']);
            $eventDay = $eventDayModel->find($item['event_day_id']);
            $seat = $seatModel->find($item['seat_id']);
            $sector = $sectorModel->find($item['sector_id']);
            
            if (!$event || !$eventDay || !$seat || !$sector) {
                continue;
            }
            
            $queue = model('QueueModel')->find($seat->queue_id);
            
            $details[] = [
                'booking_id' => $item['booking_id'],
                'event' => $event,
                'event_day' => $eventDay,
                'seat' => $seat,
                'sector' => $sector,
                'queue' => $queue,
                'price' => $item['price']
            ];
        }
        
        return $details;
    }
}
