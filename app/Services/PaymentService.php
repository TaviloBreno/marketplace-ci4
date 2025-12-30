<?php

namespace App\Services;

use App\Models\EventModel;
use App\Models\OrderModel;
use App\Models\TicketModel;
use App\Models\SeatBookingModel;
use App\Models\SectorModel;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Exception\ApiErrorException;

class PaymentService
{
    protected string $stripeSecretKey;
    protected string $stripePublicKey;
    protected OrderModel $orderModel;
    protected TicketModel $ticketModel;
    protected SeatBookingModel $seatBookingModel;
    protected SectorModel $sectorModel;
    protected EventModel $eventModel;
    
    protected const SERVICE_FEE_PERCENT = 0.10; // 10%
    protected const PLATFORM_FEE_PERCENT = 0.05; // 5% da plataforma

    public function __construct()
    {
        $this->stripeSecretKey = config('Stripe')->secretKey;
        $this->stripePublicKey = config('Stripe')->publishableKey;
        
        Stripe::setApiKey($this->stripeSecretKey);
        
        $this->orderModel = new OrderModel();
        $this->ticketModel = new TicketModel();
        $this->seatBookingModel = new SeatBookingModel();
        $this->sectorModel = new SectorModel();
        $this->eventModel = new EventModel();
    }

    /**
     * Obter chave pública do Stripe
     */
    public function getPublicKey(): string
    {
        return $this->stripePublicKey;
    }

    /**
     * Criar PaymentIntent para o checkout
     */
    public function createPaymentIntent(array $cartItems, int $userId): array
    {
        if (empty($cartItems)) {
            throw new \InvalidArgumentException('Carrinho vazio');
        }
        
        // Calcular totais
        $subtotal = 0;
        $itemsByOrganizer = [];
        
        foreach ($cartItems as $item) {
            $subtotal += $item['price'];
            
            // Agrupar por organizador (para split payment)
            $event = $this->eventModel->find($item['event_id']);
            if (!$event) {
                throw new \InvalidArgumentException('Evento não encontrado');
            }
            
            $organizerId = $event->user_id;
            if (!isset($itemsByOrganizer[$organizerId])) {
                $itemsByOrganizer[$organizerId] = [
                    'stripe_account_id' => $event->stripe_account_id ?? null,
                    'amount' => 0,
                    'items' => []
                ];
            }
            
            $itemsByOrganizer[$organizerId]['amount'] += $item['price'];
            $itemsByOrganizer[$organizerId]['items'][] = $item;
        }
        
        $serviceFee = $subtotal * self::SERVICE_FEE_PERCENT;
        $total = $subtotal + $serviceFee;
        $totalCents = (int) round($total * 100);
        
        // Metadados do pagamento
        $metadata = [
            'user_id' => $userId,
            'items_count' => count($cartItems),
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee
        ];
        
        // Configurar transferências para organizadores (Stripe Connect)
        $transferData = null;
        
        // Se há apenas um organizador com conta Stripe, usar transfer_data
        if (count($itemsByOrganizer) === 1) {
            $organizer = reset($itemsByOrganizer);
            if (!empty($organizer['stripe_account_id'])) {
                $platformFee = (int) round($total * self::PLATFORM_FEE_PERCENT * 100);
                $transferData = [
                    'destination' => $organizer['stripe_account_id'],
                ];
                $applicationFee = $platformFee;
            }
        }
        
        try {
            $paymentIntentData = [
                'amount' => $totalCents,
                'currency' => 'brl',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => $metadata
            ];
            
            if ($transferData) {
                $paymentIntentData['transfer_data'] = $transferData;
                $paymentIntentData['application_fee_amount'] = $applicationFee ?? null;
            }
            
            $paymentIntent = PaymentIntent::create($paymentIntentData);
            
            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $total,
                'amount_cents' => $totalCents
            ];
            
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe PaymentIntent Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Erro ao processar pagamento: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Confirmar pagamento e criar pedido
     */
    public function confirmPayment(string $paymentIntentId, array $cartItems, int $userId): array
    {
        try {
            // Verificar status do pagamento no Stripe
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            if ($paymentIntent->status !== 'succeeded') {
                return [
                    'success' => false,
                    'error' => 'Pagamento não confirmado. Status: ' . $paymentIntent->status
                ];
            }
            
            // Calcular totais
            $subtotal = array_sum(array_column($cartItems, 'price'));
            $serviceFee = $subtotal * self::SERVICE_FEE_PERCENT;
            $total = $subtotal + $serviceFee;
            
            // Obter evento do primeiro item
            $firstItem = reset($cartItems);
            $event = $this->eventModel->find($firstItem['event_id']);
            
            // Criar pedido
            $orderData = [
                'user_id' => $userId,
                'event_id' => $firstItem['event_id'],
                'organizer_id' => $event->user_id,
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'total' => $total,
                'payment_intent_id' => $paymentIntentId,
                'payment_method' => $paymentIntent->payment_method_types[0] ?? 'card',
                'status' => 'completed',
                'paid_at' => date('Y-m-d H:i:s')
            ];
            
            $this->orderModel->insert($orderData);
            $orderId = $this->orderModel->getInsertID();
            
            // Criar tickets para cada item
            $tickets = [];
            foreach ($cartItems as $item) {
                // Confirmar reserva do assento
                $this->seatBookingModel->update($item['booking_id'], [
                    'status' => 'confirmed',
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Gerar código único do ticket
                $ticketCode = $this->generateTicketCode();
                
                // Criar ticket
                $ticketData = [
                    'order_id' => $orderId,
                    'event_id' => $item['event_id'],
                    'event_day_id' => $item['event_day_id'],
                    'seat_id' => $item['seat_id'],
                    'seat_booking_id' => $item['booking_id'],
                    'user_id' => $userId,
                    'code' => $ticketCode,
                    'qr_code' => $this->generateQRCodeData($ticketCode, $item),
                    'price' => $item['price'],
                    'status' => 'active'
                ];
                
                $this->ticketModel->insert($ticketData);
                $ticketId = $this->ticketModel->getInsertID();
                
                $tickets[] = [
                    'id' => $ticketId,
                    'code' => $ticketCode,
                    'item' => $item
                ];
            }
            
            return [
                'success' => true,
                'order_id' => $orderId,
                'tickets' => $tickets,
                'total' => $total
            ];
            
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe Confirm Payment Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Erro ao confirmar pagamento: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            log_message('error', 'Confirm Payment Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Erro ao processar pedido: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Processar reembolso
     */
    public function processRefund(int $orderId, ?float $amount = null, string $reason = ''): array
    {
        $order = $this->orderModel->find($orderId);
        
        if (!$order) {
            return ['success' => false, 'error' => 'Pedido não encontrado'];
        }
        
        if ($order->status === 'refunded') {
            return ['success' => false, 'error' => 'Pedido já foi reembolsado'];
        }
        
        if (empty($order->payment_intent_id)) {
            return ['success' => false, 'error' => 'Pagamento não encontrado'];
        }
        
        try {
            $refundData = [
                'payment_intent' => $order->payment_intent_id,
            ];
            
            // Se valor específico informado, fazer reembolso parcial
            if ($amount !== null && $amount > 0 && $amount < $order->total) {
                $refundData['amount'] = (int) round($amount * 100);
            }
            
            if (!empty($reason)) {
                $refundData['reason'] = 'requested_by_customer';
                $refundData['metadata'] = ['reason_details' => $reason];
            }
            
            $refund = Refund::create($refundData);
            
            // Atualizar status do pedido
            $refundedAmount = $refund->amount / 100;
            $newStatus = $refundedAmount >= $order->total ? 'refunded' : 'partial_refund';
            
            $this->orderModel->update($orderId, [
                'status' => $newStatus,
                'refund_id' => $refund->id,
                'refund_amount' => $refundedAmount,
                'refunded_at' => date('Y-m-d H:i:s')
            ]);
            
            // Cancelar tickets e liberar assentos
            if ($newStatus === 'refunded') {
                $tickets = $this->ticketModel->where('order_id', $orderId)->findAll();
                
                foreach ($tickets as $ticket) {
                    // Cancelar ticket
                    $this->ticketModel->update($ticket->id, [
                        'status' => 'cancelled',
                        'cancelled_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Liberar assento
                    if ($ticket->seat_booking_id) {
                        $this->seatBookingModel->update($ticket->seat_booking_id, [
                            'status' => 'cancelled',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            return [
                'success' => true,
                'refund_id' => $refund->id,
                'refund_amount' => $refundedAmount,
                'status' => $newStatus
            ];
            
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe Refund Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Erro ao processar reembolso: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar elegibilidade para reembolso
     */
    public function checkRefundEligibility(int $orderId): array
    {
        $order = $this->orderModel->find($orderId);
        
        if (!$order) {
            return ['eligible' => false, 'reason' => 'Pedido não encontrado'];
        }
        
        if ($order->status === 'refunded') {
            return ['eligible' => false, 'reason' => 'Pedido já foi reembolsado'];
        }
        
        if ($order->status !== 'completed') {
            return ['eligible' => false, 'reason' => 'Pedido não está em status válido para reembolso'];
        }
        
        // Verificar se o evento ainda não aconteceu
        $tickets = $this->ticketModel->where('order_id', $orderId)->findAll();
        
        if (empty($tickets)) {
            return ['eligible' => false, 'reason' => 'Nenhum ingresso encontrado'];
        }
        
        $eventDay = model('EventDayModel')->find($tickets[0]->event_day_id);
        
        if (!$eventDay) {
            return ['eligible' => false, 'reason' => 'Data do evento não encontrada'];
        }
        
        $eventDate = new \DateTime($eventDay->event_date);
        $now = new \DateTime();
        $diff = $now->diff($eventDate);
        
        // Verificar se faltam pelo menos 7 dias para o evento
        if ($eventDate <= $now) {
            return ['eligible' => false, 'reason' => 'O evento já aconteceu'];
        }
        
        if ($diff->days < 7) {
            return [
                'eligible' => false, 
                'reason' => 'Reembolsos só são permitidos até 7 dias antes do evento'
            ];
        }
        
        return [
            'eligible' => true,
            'order' => $order,
            'days_until_event' => $diff->days
        ];
    }

    /**
     * Gerar código único do ticket
     */
    protected function generateTicketCode(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        
        for ($i = 0; $i < 12; $i++) {
            if ($i > 0 && $i % 4 === 0) {
                $code .= '-';
            }
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Verificar unicidade
        $exists = $this->ticketModel->where('code', $code)->first();
        if ($exists) {
            return $this->generateTicketCode();
        }
        
        return $code;
    }

    /**
     * Gerar dados do QR Code
     */
    protected function generateQRCodeData(string $ticketCode, array $item): string
    {
        return json_encode([
            'code' => $ticketCode,
            'event_id' => $item['event_id'],
            'event_day_id' => $item['event_day_id'],
            'seat_id' => $item['seat_id'],
            'generated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Calcular totais do carrinho
     */
    public function calculateTotals(array $cartItems): array
    {
        $subtotal = array_sum(array_column($cartItems, 'price'));
        $serviceFee = $subtotal * self::SERVICE_FEE_PERCENT;
        $total = $subtotal + $serviceFee;
        
        return [
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'total' => $total,
            'items_count' => count($cartItems)
        ];
    }
}
