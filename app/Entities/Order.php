<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Order extends Entity
{
    protected $datamap = [];
    protected $dates   = ['paid_at', 'cancelled_at', 'refunded_at', 'created_at', 'updated_at'];
    protected $casts   = [
        'id'           => 'integer',
        'user_id'      => 'integer',
        'event_id'     => 'integer',
        'event_day_id' => 'integer',
        'subtotal'     => 'float',
        'service_fee'  => 'float',
        'discount'     => 'float',
        'total'        => 'float',
    ];

    /**
     * Gera um número de pedido único
     */
    public static function generateOrderNumber(): string
    {
        return strtoupper(date('Ymd') . '-' . bin2hex(random_bytes(4)));
    }

    /**
     * Retorna o subtotal formatado
     */
    public function getFormattedSubtotal(): string
    {
        return 'R$ ' . number_format($this->attributes['subtotal'], 2, ',', '.');
    }

    /**
     * Retorna a taxa de serviço formatada
     */
    public function getFormattedServiceFee(): string
    {
        return 'R$ ' . number_format($this->attributes['service_fee'], 2, ',', '.');
    }

    /**
     * Retorna o desconto formatado
     */
    public function getFormattedDiscount(): string
    {
        return 'R$ ' . number_format($this->attributes['discount'], 2, ',', '.');
    }

    /**
     * Retorna o total formatado
     */
    public function getFormattedTotal(): string
    {
        return 'R$ ' . number_format($this->attributes['total'], 2, ',', '.');
    }

    /**
     * Retorna o badge de status
     */
    public function getStatusBadge(): string
    {
        $badges = [
            'pending'    => '<span class="badge bg-warning">Pendente</span>',
            'processing' => '<span class="badge bg-info">Processando</span>',
            'paid'       => '<span class="badge bg-success">Pago</span>',
            'cancelled'  => '<span class="badge bg-danger">Cancelado</span>',
            'refunded'   => '<span class="badge bg-secondary">Reembolsado</span>',
        ];
        return $badges[$this->attributes['status']] ?? '';
    }

    /**
     * Verifica se o pedido está pago
     */
    public function isPaid(): bool
    {
        return $this->attributes['status'] === 'paid';
    }

    /**
     * Verifica se pode ser cancelado
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->attributes['status'], ['pending', 'processing']);
    }

    /**
     * Verifica se pode ser reembolsado
     */
    public function canBeRefunded(): bool
    {
        return $this->attributes['status'] === 'paid';
    }
}
