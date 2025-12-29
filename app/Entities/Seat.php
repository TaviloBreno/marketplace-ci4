<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Seat extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'             => 'integer',
        'row_id'         => 'integer',
        'sector_id'      => 'integer',
        'position_x'     => 'integer',
        'position_y'     => 'integer',
        'is_wheelchair'  => 'boolean',
        'is_companion'   => 'boolean',
        'price_override' => '?float',
    ];

    /**
     * Retorna o preço do assento (override ou do setor)
     */
    public function getPrice(?float $sectorPrice = null): float
    {
        if ($this->attributes['price_override'] !== null) {
            return (float) $this->attributes['price_override'];
        }
        return $sectorPrice ?? 0.0;
    }

    /**
     * Verifica se o assento está disponível
     */
    public function isAvailable(): bool
    {
        return $this->attributes['status'] === 'available';
    }

    /**
     * Verifica se o assento está vendido
     */
    public function isSold(): bool
    {
        return $this->attributes['status'] === 'sold';
    }

    /**
     * Verifica se o assento está reservado
     */
    public function isReserved(): bool
    {
        return $this->attributes['status'] === 'reserved';
    }

    /**
     * Verifica se o assento está bloqueado
     */
    public function isBlocked(): bool
    {
        return $this->attributes['status'] === 'blocked';
    }

    /**
     * Retorna a classe CSS baseada no status
     */
    public function getStatusClass(): string
    {
        $classes = [
            'available' => 'seat-available',
            'reserved'  => 'seat-reserved',
            'sold'      => 'seat-sold',
            'blocked'   => 'seat-blocked',
        ];
        
        $class = $classes[$this->attributes['status']] ?? 'seat-available';
        
        if ($this->attributes['is_wheelchair']) {
            $class .= ' seat-wheelchair';
        }
        
        if ($this->attributes['is_companion']) {
            $class .= ' seat-companion';
        }
        
        return $class;
    }

    /**
     * Retorna o ícone do assento
     */
    public function getIcon(): string
    {
        if ($this->attributes['is_wheelchair']) {
            return '<i class="bi bi-universal-access"></i>';
        }
        return '';
    }

    /**
     * Retorna o estilo de posicionamento
     */
    public function getPositionStyle(): string
    {
        return sprintf(
            'left: %dpx; top: %dpx;',
            $this->attributes['position_x'],
            $this->attributes['position_y']
        );
    }

    /**
     * Retorna os atributos de dados para JavaScript
     */
    public function getDataAttributes(): string
    {
        return sprintf(
            'data-seat-id="%d" data-row-id="%d" data-sector-id="%d" data-seat-label="%s" data-status="%s"',
            $this->attributes['id'],
            $this->attributes['row_id'],
            $this->attributes['sector_id'],
            $this->attributes['seat_label'],
            $this->attributes['status']
        );
    }
}
