<?php

namespace App\Controllers;

use App\Models\TicketModel;
use App\Models\EventModel;
use App\Models\EventDayModel;
use App\Models\SeatModel;
use App\Models\SectorModel;
use App\Models\OrderModel;

class TicketController extends BaseController
{
    protected TicketModel $ticketModel;
    protected EventModel $eventModel;
    protected OrderModel $orderModel;

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
        $this->eventModel = new EventModel();
        $this->orderModel = new OrderModel();
    }

    /**
     * Listar todos os ingressos do usuário
     */
    public function index()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login?redirect=' . urlencode(current_url()));
        }

        $tickets = $this->ticketModel
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Enriquecer dados
        $seatModel = new SeatModel();
        $sectorModel = new SectorModel();
        $eventDayModel = new EventDayModel();

        $ticketGroups = [];

        foreach ($tickets as $ticket) {
            $event = $this->eventModel->find($ticket->event_id);
            $eventDay = $eventDayModel->find($ticket->event_day_id);
            $seat = $seatModel->find($ticket->seat_id);
            $queue = null;
            $sector = null;

            if ($seat) {
                $queue = model('QueueModel')->find($seat->queue_id);
                if ($queue) {
                    $sector = $sectorModel->find($queue->sector_id);
                }
            }

            $groupKey = $ticket->event_id . '_' . $ticket->event_day_id;

            if (!isset($ticketGroups[$groupKey])) {
                $ticketGroups[$groupKey] = [
                    'event' => $event,
                    'event_day' => $eventDay,
                    'tickets' => []
                ];
            }

            $ticketGroups[$groupKey]['tickets'][] = [
                'ticket' => $ticket,
                'seat' => $seat,
                'queue' => $queue,
                'sector' => $sector
            ];
        }

        // Separar em eventos futuros e passados
        $upcoming = [];
        $past = [];
        $today = date('Y-m-d');

        foreach ($ticketGroups as $group) {
            if ($group['event_day'] && $group['event_day']->event_date >= $today) {
                $upcoming[] = $group;
            } else {
                $past[] = $group;
            }
        }

        // Ordenar por data
        usort($upcoming, function ($a, $b) {
            return strtotime($a['event_day']->event_date) - strtotime($b['event_day']->event_date);
        });

        return view('public/tickets/index', [
            'upcoming' => $upcoming,
            'past' => $past
        ]);
    }

    /**
     * Ver ingresso individual
     */
    public function show(string $code)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $ticket = $this->ticketModel
            ->where('code', $code)
            ->where('user_id', auth()->id())
            ->first();

        if (!$ticket) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $event = $this->eventModel->find($ticket->event_id);
        $eventDayModel = new EventDayModel();
        $eventDay = $eventDayModel->find($ticket->event_day_id);

        $seatModel = new SeatModel();
        $seat = $seatModel->find($ticket->seat_id);
        $queue = null;
        $sector = null;

        if ($seat) {
            $queue = model('QueueModel')->find($seat->queue_id);
            if ($queue) {
                $sectorModel = new SectorModel();
                $sector = $sectorModel->find($queue->sector_id);
            }
        }

        $order = $this->orderModel->find($ticket->order_id);

        return view('public/tickets/show', [
            'ticket' => $ticket,
            'event' => $event,
            'eventDay' => $eventDay,
            'seat' => $seat,
            'queue' => $queue,
            'sector' => $sector,
            'order' => $order
        ]);
    }

    /**
     * Página de impressão do ingresso
     */
    public function print(string $code)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $ticket = $this->ticketModel
            ->where('code', $code)
            ->where('user_id', auth()->id())
            ->first();

        if (!$ticket) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $event = $this->eventModel->find($ticket->event_id);
        $eventDayModel = new EventDayModel();
        $eventDay = $eventDayModel->find($ticket->event_day_id);

        $seatModel = new SeatModel();
        $seat = $seatModel->find($ticket->seat_id);
        $queue = null;
        $sector = null;

        if ($seat) {
            $queue = model('QueueModel')->find($seat->queue_id);
            if ($queue) {
                $sectorModel = new SectorModel();
                $sector = $sectorModel->find($queue->sector_id);
            }
        }

        return view('public/tickets/print', [
            'ticket' => $ticket,
            'event' => $event,
            'eventDay' => $eventDay,
            'seat' => $seat,
            'queue' => $queue,
            'sector' => $sector
        ]);
    }

    /**
     * Download PDF do ingresso
     */
    public function download(string $code)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $ticket = $this->ticketModel
            ->where('code', $code)
            ->where('user_id', auth()->id())
            ->first();

        if (!$ticket) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Por enquanto, redirecionar para a versão de impressão
        // Em produção, usar uma biblioteca PDF como DOMPDF ou TCPDF
        return redirect()->to("ingresso/{$code}/imprimir");
    }

    /**
     * API: Verificar validade do ingresso (para leitores de QR Code)
     */
    public function verify(string $code)
    {
        $ticket = $this->ticketModel->where('code', $code)->first();

        if (!$ticket) {
            return $this->response->setJSON([
                'valid' => false,
                'message' => 'Ingresso não encontrado'
            ])->setStatusCode(404);
        }

        if ($ticket->status !== 'active') {
            return $this->response->setJSON([
                'valid' => false,
                'message' => 'Ingresso ' . ($ticket->status === 'used' ? 'já utilizado' : 'cancelado')
            ])->setStatusCode(400);
        }

        $event = $this->eventModel->find($ticket->event_id);
        $eventDayModel = new EventDayModel();
        $eventDay = $eventDayModel->find($ticket->event_day_id);

        $seatModel = new SeatModel();
        $seat = $seatModel->find($ticket->seat_id);

        return $this->response->setJSON([
            'valid' => true,
            'ticket' => [
                'code' => $ticket->code,
                'seat' => $seat ? $seat->code : 'N/A'
            ],
            'event' => [
                'title' => $event ? $event->title : 'N/A',
                'date' => $eventDay ? $eventDay->event_date : 'N/A',
                'venue' => $event ? $event->venue : 'N/A'
            ]
        ]);
    }

    /**
     * API: Marcar ingresso como usado (check-in)
     */
    public function checkIn(string $code)
    {
        // Esta rota seria protegida para organizadores/staff apenas
        $ticket = $this->ticketModel->where('code', $code)->first();

        if (!$ticket) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ingresso não encontrado'
            ])->setStatusCode(404);
        }

        if ($ticket->status === 'used') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ingresso já foi utilizado',
                'used_at' => $ticket->used_at
            ])->setStatusCode(400);
        }

        if ($ticket->status !== 'active') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ingresso não está ativo'
            ])->setStatusCode(400);
        }

        // Marcar como usado
        $this->ticketModel->update($ticket->id, [
            'status' => 'used',
            'used_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Check-in realizado com sucesso'
        ]);
    }
}
