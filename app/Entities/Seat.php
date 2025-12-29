<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Seat extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'queue_id'      => 'integer',
        'position_x'    => 'integer',
        'position_y'    => 'integer',
        'is_accessible' => 'boolean',
        'sort_order'    => 'integer',
    ];

    /**
     * Retorna o label do assento
     */
    public function getLabel(): string
    {
        return $this->attributes['label'] ?? $this->attributes['number'];
    }

    /**
     * Verifica se o assento está disponível
     */
    public function isAvailable(): bool
    {
        return $this->attributes['status'] === 'available';
    }

    /**
     * Retorna a classe CSS do status
     */
    public function getStatusClass(): string
    {
        $classes = [
            'available'   => 'seat-available',
            'blocked'     => 'seat-blocked',
            'maintenance' => 'seat-maintenance',
        ];

        return $classes[$this->attributes['status']] ?? 'seat-available';
    }
}
