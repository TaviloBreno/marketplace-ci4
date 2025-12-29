<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class SeatBooking extends Entity
{
    protected $datamap = [];
    protected $dates   = ['reserved_at', 'expires_at', 'confirmed_at', 'created_at', 'updated_at'];
    protected $casts   = [
        'id'           => 'integer',
        'seat_id'      => 'integer',
        'event_day_id' => 'integer',
        'user_id'      => 'integer',
        'price'        => 'float',
    ];

    /**
     * Verifica se a reserva está expirada
     */
    public function isExpired(): bool
    {
        if ($this->attributes['status'] !== 'reserved') {
            return false;
        }

        if (empty($this->attributes['expires_at'])) {
            return false;
        }

        return strtotime($this->attributes['expires_at']) < time();
    }

    /**
     * Verifica se a reserva está confirmada
     */
    public function isConfirmed(): bool
    {
        return $this->attributes['status'] === 'confirmed';
    }

    /**
     * Retorna o tempo restante da reserva em minutos
     */
    public function getRemainingMinutes(): int
    {
        if (empty($this->attributes['expires_at'])) {
            return 0;
        }

        $remaining = strtotime($this->attributes['expires_at']) - time();
        return max(0, ceil($remaining / 60));
    }

    /**
     * Retorna o preço formatado
     */
    public function getFormattedPrice(): string
    {
        return 'R$ ' . number_format($this->attributes['price'], 2, ',', '.');
    }

    /**
     * Retorna o label do status
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'reserved'  => 'Reservado',
            'confirmed' => 'Confirmado',
            'cancelled' => 'Cancelado',
            'expired'   => 'Expirado',
        ];

        return $labels[$this->attributes['status']] ?? $this->attributes['status'];
    }
}
