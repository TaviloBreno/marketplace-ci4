<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\TicketModel;
use App\Models\EventModel;
use App\Models\EventDayModel;
use App\Models\SeatModel;
use App\Models\SectorModel;
use App\Services\PaymentService;
use CodeIgniter\HTTP\ResponseInterface;

class OrderController extends BaseController
{
    protected OrderModel $orderModel;
    protected TicketModel $ticketModel;
    protected EventModel $eventModel;
    protected PaymentService $paymentService;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->ticketModel = new TicketModel();
        $this->eventModel = new EventModel();
        $this->paymentService = new PaymentService();
    }

    /**
     * Listar pedidos do usuário
     */
    public function index()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login?redirect=' . urlencode(current_url()));
        }

        $orders = $this->orderModel
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Enriquecer dados dos pedidos
        foreach ($orders as &$order) {
            $order->event = $this->eventModel->find($order->event_id);
            $order->tickets = $this->ticketModel->where('order_id', $order->id)->findAll();
            $order->tickets_count = count($order->tickets);
            
            // Pegar data do primeiro ticket
            if (!empty($order->tickets)) {
                $eventDayModel = new EventDayModel();
                $order->event_day = $eventDayModel->find($order->tickets[0]->event_day_id);
            }
        }

        return view('public/orders/index', [
            'orders' => $orders
        ]);
    }

    /**
     * Detalhes do pedido
     */
    public function show(int $orderId)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $order = $this->orderModel
            ->where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Dados do evento
        $event = $this->eventModel->find($order->event_id);
        
        // Tickets com detalhes
        $tickets = $this->ticketModel->where('order_id', $orderId)->findAll();
        $ticketDetails = [];
        
        $seatModel = new SeatModel();
        $sectorModel = new SectorModel();
        $eventDayModel = new EventDayModel();
        
        foreach ($tickets as $ticket) {
            $seat = $seatModel->find($ticket->seat_id);
            $eventDay = $eventDayModel->find($ticket->event_day_id);
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
                'sector' => $sector,
                'event_day' => $eventDay
            ];
        }
        
        // Verificar elegibilidade para reembolso
        $refundEligibility = $this->paymentService->checkRefundEligibility($orderId);

        return view('public/orders/show', [
            'order' => $order,
            'event' => $event,
            'tickets' => $ticketDetails,
            'refundEligibility' => $refundEligibility
        ]);
    }

    /**
     * Solicitar reembolso
     */
    public function requestRefund(int $orderId)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $order = $this->orderModel
            ->where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Verificar elegibilidade
        $eligibility = $this->paymentService->checkRefundEligibility($orderId);

        if (!$eligibility['eligible']) {
            return redirect()->to("meus-pedidos/{$orderId}")
                ->with('error', $eligibility['reason']);
        }

        // Obter dados do evento
        $event = $this->eventModel->find($order->event_id);

        return view('public/orders/refund', [
            'order' => $order,
            'event' => $event,
            'eligibility' => $eligibility
        ]);
    }

    /**
     * Processar reembolso
     */
    public function processRefund(int $orderId): ResponseInterface
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Usuário não autenticado'
            ])->setStatusCode(401);
        }

        $order = $this->orderModel
            ->where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Pedido não encontrado'
            ])->setStatusCode(404);
        }

        // Verificar elegibilidade
        $eligibility = $this->paymentService->checkRefundEligibility($orderId);

        if (!$eligibility['eligible']) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $eligibility['reason']
            ])->setStatusCode(400);
        }

        $reason = $this->request->getPost('reason') ?? 'Solicitado pelo cliente';

        // Processar reembolso
        $result = $this->paymentService->processRefund($orderId, null, $reason);

        if ($result['success']) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Reembolso processado com sucesso',
                'refund_amount' => $result['refund_amount'],
                'redirect_url' => base_url("meus-pedidos/{$orderId}")
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'error' => $result['error'] ?? 'Erro ao processar reembolso'
        ])->setStatusCode(400);
    }

    /**
     * Download do comprovante
     */
    public function receipt(int $orderId)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $order = $this->orderModel
            ->where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $event = $this->eventModel->find($order->event_id);
        $tickets = $this->ticketModel->where('order_id', $orderId)->findAll();
        
        $eventDay = null;
        if (!empty($tickets)) {
            $eventDayModel = new EventDayModel();
            $eventDay = $eventDayModel->find($tickets[0]->event_day_id);
        }

        return view('public/orders/receipt', [
            'order' => $order,
            'event' => $event,
            'eventDay' => $eventDay,
            'tickets' => $tickets,
            'user' => auth()->user()
        ]);
    }
}
