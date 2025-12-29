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
        'user_id'      => '?integer',
        'order_id'     => '?integer',
        'price'        => 'float',
    ];

    /**
     * Verifica se a reserva expirou
     */
    public function isExpired(): bool
    {
        if (empty($this->attributes['expires_at'])) {
            return false;
        }
        
        $expiresAt = new \DateTime($this->attributes['expires_at']);
        $now = new \DateTime();
        
        return $now > $expiresAt;
    }

    /**
     * Verifica se está confirmado
     */
    public function isConfirmed(): bool
    {
        return $this->attributes['status'] === 'confirmed';
    }

    /**
     * Verifica se está reservado
     */
    public function isReserved(): bool
    {
        return $this->attributes['status'] === 'reserved';
    }

    /**
     * Retorna o tempo restante da reserva
     */
    public function getTimeRemaining(): ?int
    {
        if (empty($this->attributes['expires_at'])) {
            return null;
        }
        
        $expiresAt = new \DateTime($this->attributes['expires_at']);
        $now = new \DateTime();
        
        $diff = $expiresAt->getTimestamp() - $now->getTimestamp();
        
        return max(0, $diff);
    }

    /**
     * Retorna o tempo restante formatado
     */
    public function getFormattedTimeRemaining(): string
    {
        $seconds = $this->getTimeRemaining();
        
        if ($seconds === null || $seconds <= 0) {
            return 'Expirado';
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Retorna o preço formatado
     */
    public function getFormattedPrice(): string
    {
        return 'R$ ' . number_format($this->attributes['price'], 2, ',', '.');
    }
}
