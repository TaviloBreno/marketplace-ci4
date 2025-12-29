<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Ticket extends Entity
{
    protected $datamap = [];
    protected $dates   = ['used_at', 'created_at', 'updated_at'];
    protected $casts   = [
        'id'              => 'integer',
        'order_id'        => 'integer',
        'seat_booking_id' => 'integer',
    ];

    /**
     * Gera um código único para o ticket
     */
    public static function generateTicketCode(): string
    {
        return 'TKT-' . strtoupper(bin2hex(random_bytes(6)));
    }

    /**
     * Verifica se o ticket pode ser usado
     */
    public function canUse(): bool
    {
        return $this->attributes['status'] === 'active';
    }

    /**
     * Verifica se o ticket já foi usado
     */
    public function isUsed(): bool
    {
        return $this->attributes['status'] === 'used';
    }

    /**
     * Retorna o label do status
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'active'      => 'Ativo',
            'used'        => 'Utilizado',
            'cancelled'   => 'Cancelado',
            'transferred' => 'Transferido',
        ];

        return $labels[$this->attributes['status']] ?? $this->attributes['status'];
    }

    /**
     * Retorna a classe CSS do status
     */
    public function getStatusClass(): string
    {
        $classes = [
            'active'      => 'success',
            'used'        => 'secondary',
            'cancelled'   => 'danger',
            'transferred' => 'info',
        ];

        return $classes[$this->attributes['status']] ?? 'secondary';
    }
}
