<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\EventDayModel;
use App\Models\SectorModel;
use App\Models\SeatModel;
use App\Models\SeatBookingModel;
use CodeIgniter\HTTP\ResponseInterface;

class CartController extends BaseController
{
    protected EventModel $eventModel;
    protected EventDayModel $eventDayModel;
    protected SectorModel $sectorModel;
    protected SeatModel $seatModel;
    protected SeatBookingModel $seatBookingModel;
    
    protected const SESSION_KEY = 'cart_items';
    protected const MAX_ITEMS = 6;
    protected const RESERVATION_MINUTES = 10;
    
    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->eventDayModel = new EventDayModel();
        $this->sectorModel = new SectorModel();
        $this->seatModel = new SeatModel();
        $this->seatBookingModel = new SeatBookingModel();
    }

    /**
     * Exibir o carrinho
     */
    public function index()
    {
        $cartItems = $this->getCartItems();
        $cartDetails = $this->getCartDetails($cartItems);
        
        // Remover itens expirados
        $this->cleanupExpiredItems();
        
        // Recalcular totais
        $subtotal = 0;
        foreach ($cartDetails as $item) {
            $subtotal += $item['price'];
        }
        
        $serviceFee = $subtotal * 0.10; // 10% de taxa
        $total = $subtotal + $serviceFee;
        
        return view('public/cart/index', [
            'items' => $cartDetails,
            'subtotal' => $subtotal,
            'serviceFee' => $serviceFee,
            'total' => $total
        ]);
    }

    /**
     * API: Adicionar itens ao carrinho
     */
    public function add(): ResponseInterface
    {
        $json = $this->request->getJSON(true);
        
        if (!$json) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dados inválidos'
            ])->setStatusCode(400);
        }
        
        $eventId = $json['event_id'] ?? null;
        $eventDayId = $json['event_day_id'] ?? null;
        $seats = $json['seats'] ?? [];
        
        if (!$eventId || !$eventDayId || empty($seats)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dados incompletos'
            ])->setStatusCode(400);
        }
        
        // Verificar evento
        $event = $this->eventModel->find($eventId);
        if (!$event || $event->status !== 'published') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Evento não encontrado ou não disponível'
            ])->setStatusCode(404);
        }
        
        // Verificar dia do evento
        $eventDay = $this->eventDayModel->find($eventDayId);
        if (!$eventDay || $eventDay->event_id != $eventId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data do evento não encontrada'
            ])->setStatusCode(404);
        }
        
        // Verificar se a data não passou
        if ($eventDay->event_date < date('Y-m-d')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Esta data já passou'
            ])->setStatusCode(400);
        }
        
        // Obter itens atuais do carrinho
        $cartItems = $this->getCartItems();
        
        // Verificar limite de itens
        if (count($cartItems) + count($seats) > self::MAX_ITEMS) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Limite de ' . self::MAX_ITEMS . ' ingressos por compra'
            ])->setStatusCode(400);
        }
        
        // Verificar disponibilidade de cada assento
        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::RESERVATION_MINUTES . ' minutes'));
        
        $addedItems = [];
        
        foreach ($seats as $seatData) {
            $seatId = $seatData['seat_id'] ?? null;
            $sectorId = $seatData['sector_id'] ?? null;
            
            if (!$seatId || !$sectorId) {
                continue;
            }
            
            // Verificar se o assento existe
            $seat = $this->seatModel->find($seatId);
            if (!$seat) {
                continue;
            }
            
            // Verificar setor e preço
            $sector = $this->sectorModel->find($sectorId);
            if (!$sector || $sector->event_id != $eventId) {
                continue;
            }
            
            // Verificar se já não está reservado ou comprado
            $existingBooking = $this->seatBookingModel
                ->where('seat_id', $seatId)
                ->where('event_day_id', $eventDayId)
                ->groupStart()
                    ->where('status', 'confirmed')
                    ->orGroupStart()
                        ->where('status', 'reserved')
                        ->where('reserved_until >', $now)
                    ->groupEnd()
                ->groupEnd()
                ->first();
            
            if ($existingBooking) {
                continue; // Pular assento já reservado
            }
            
            // Verificar se já está no carrinho
            $alreadyInCart = false;
            foreach ($cartItems as $item) {
                if ($item['seat_id'] == $seatId && $item['event_day_id'] == $eventDayId) {
                    $alreadyInCart = true;
                    break;
                }
            }
            
            if ($alreadyInCart) {
                continue;
            }
            
            // Criar reserva temporária
            $bookingData = [
                'seat_id' => $seatId,
                'event_day_id' => $eventDayId,
                'user_id' => auth()->loggedIn() ? auth()->id() : null,
                'status' => 'reserved',
                'reserved_until' => $expiresAt,
                'created_at' => $now,
                'updated_at' => $now
            ];
            
            $this->seatBookingModel->insert($bookingData);
            $bookingId = $this->seatBookingModel->getInsertID();
            
            // Adicionar ao carrinho
            $cartItem = [
                'booking_id' => $bookingId,
                'event_id' => $eventId,
                'event_day_id' => $eventDayId,
                'seat_id' => $seatId,
                'sector_id' => $sectorId,
                'price' => $sector->price,
                'added_at' => $now,
                'expires_at' => $expiresAt
            ];
            
            $cartItems[] = $cartItem;
            $addedItems[] = $cartItem;
        }
        
        // Salvar carrinho na sessão
        $this->saveCartItems($cartItems);
        
        if (empty($addedItems)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nenhum assento disponível foi adicionado'
            ])->setStatusCode(400);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'message' => count($addedItems) . ' assento(s) adicionado(s) ao carrinho',
            'cart_count' => count($cartItems)
        ]);
    }

    /**
     * API: Remover item do carrinho
     */
    public function remove(): ResponseInterface
    {
        $json = $this->request->getJSON(true);
        $bookingId = $json['booking_id'] ?? $this->request->getPost('booking_id');
        
        if (!$bookingId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID do item não informado'
            ])->setStatusCode(400);
        }
        
        $cartItems = $this->getCartItems();
        $found = false;
        
        foreach ($cartItems as $key => $item) {
            if ($item['booking_id'] == $bookingId) {
                // Cancelar a reserva
                $this->seatBookingModel->update($bookingId, [
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                unset($cartItems[$key]);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Item não encontrado no carrinho'
            ])->setStatusCode(404);
        }
        
        // Reindexar array
        $cartItems = array_values($cartItems);
        $this->saveCartItems($cartItems);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Item removido do carrinho',
            'cart_count' => count($cartItems)
        ]);
    }

    /**
     * API: Limpar carrinho
     */
    public function clear(): ResponseInterface
    {
        $cartItems = $this->getCartItems();
        
        // Cancelar todas as reservas
        foreach ($cartItems as $item) {
            $this->seatBookingModel->update($item['booking_id'], [
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $this->saveCartItems([]);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Carrinho limpo',
            'cart_count' => 0
        ]);
    }

    /**
     * API: Obter contagem do carrinho
     */
    public function getCount(): ResponseInterface
    {
        $this->cleanupExpiredItems();
        $cartItems = $this->getCartItems();
        
        return $this->response->setJSON([
            'count' => count($cartItems)
        ]);
    }

    /**
     * Obter itens do carrinho da sessão
     */
    protected function getCartItems(): array
    {
        return session()->get(self::SESSION_KEY) ?? [];
    }

    /**
     * Salvar itens do carrinho na sessão
     */
    protected function saveCartItems(array $items): void
    {
        session()->set(self::SESSION_KEY, $items);
    }

    /**
     * Obter detalhes completos dos itens do carrinho
     */
    protected function getCartDetails(array $cartItems): array
    {
        $details = [];
        
        foreach ($cartItems as $item) {
            $event = $this->eventModel->find($item['event_id']);
            $eventDay = $this->eventDayModel->find($item['event_day_id']);
            $seat = $this->seatModel->find($item['seat_id']);
            $sector = $this->sectorModel->find($item['sector_id']);
            
            if (!$event || !$eventDay || !$seat || !$sector) {
                continue;
            }
            
            // Obter nome da fila
            $queue = model('QueueModel')->find($seat->queue_id);
            
            $details[] = [
                'booking_id' => $item['booking_id'],
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'slug' => $event->slug,
                    'image' => $event->image,
                    'venue' => $event->venue
                ],
                'event_day' => [
                    'id' => $eventDay->id,
                    'date' => $eventDay->event_date,
                    'start_time' => $eventDay->start_time
                ],
                'seat' => [
                    'id' => $seat->id,
                    'code' => $seat->code,
                    'queue' => $queue ? $queue->name : ''
                ],
                'sector' => [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'color' => $sector->color
                ],
                'price' => $item['price'],
                'expires_at' => $item['expires_at']
            ];
        }
        
        return $details;
    }

    /**
     * Limpar itens expirados do carrinho
     */
    protected function cleanupExpiredItems(): void
    {
        $cartItems = $this->getCartItems();
        $now = date('Y-m-d H:i:s');
        $validItems = [];
        
        foreach ($cartItems as $item) {
            // Verificar se expirou
            if ($item['expires_at'] <= $now) {
                // Cancelar a reserva
                $this->seatBookingModel->update($item['booking_id'], [
                    'status' => 'cancelled',
                    'updated_at' => $now
                ]);
                continue;
            }
            
            // Verificar se a reserva ainda existe e está válida
            $booking = $this->seatBookingModel->find($item['booking_id']);
            if (!$booking || $booking->status !== 'reserved') {
                continue;
            }
            
            $validItems[] = $item;
        }
        
        $this->saveCartItems($validItems);
    }
}
