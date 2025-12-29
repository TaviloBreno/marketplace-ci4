<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Order extends Entity
{
    protected $datamap = [];
    protected $dates   = ['paid_at', 'cancelled_at', 'created_at', 'updated_at'];
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
        return 'ORD-' . strtoupper(bin2hex(random_bytes(8)));
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
     * Verifica se o pedido está pago
     */
    public function isPaid(): bool
    {
        return $this->attributes['status'] === 'paid';
    }

    /**
     * Retorna o label do status
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'pending'    => 'Pendente',
            'processing' => 'Processando',
            'paid'       => 'Pago',
            'cancelled'  => 'Cancelado',
            'refunded'   => 'Reembolsado',
        ];

        return $labels[$this->attributes['status']] ?? $this->attributes['status'];
    }

    /**
     * Retorna a classe CSS do status
     */
    public function getStatusClass(): string
    {
        $classes = [
            'pending'    => 'warning',
            'processing' => 'info',
            'paid'       => 'success',
            'cancelled'  => 'danger',
            'refunded'   => 'secondary',
        ];

        return $classes[$this->attributes['status']] ?? 'secondary';
    }
}
