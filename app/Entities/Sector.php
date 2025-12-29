<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Sector extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'         => 'integer',
        'event_id'   => 'integer',
        'price'      => 'float',
        'capacity'   => 'integer',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'width'      => 'integer',
        'height'     => 'integer',
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    protected $rows;

    /**
     * Retorna o preço formatado
     */
    public function getFormattedPrice(): string
    {
        return 'R$ ' . number_format($this->attributes['price'], 2, ',', '.');
    }

    /**
     * Retorna o total de assentos vendidos
     */
    public function getSoldSeatsCount(): int
    {
        $seatModel = model('SeatModel');
        return $seatModel->where('sector_id', $this->attributes['id'])
                         ->where('status', 'sold')
                         ->countAllResults();
    }

    /**
     * Retorna o total de assentos disponíveis
     */
    public function getAvailableSeatsCount(): int
    {
        $seatModel = model('SeatModel');
        return $seatModel->where('sector_id', $this->attributes['id'])
                         ->where('status', 'available')
                         ->countAllResults();
    }

    /**
     * Retorna o estilo CSS para posicionamento
     */
    public function getPositionStyle(): string
    {
        return sprintf(
            'left: %dpx; top: %dpx; width: %dpx; height: %dpx; background-color: %s;',
            $this->attributes['position_x'],
            $this->attributes['position_y'],
            $this->attributes['width'],
            $this->attributes['height'],
            $this->attributes['color']
        );
    }

    /**
     * Retorna as cores disponíveis para setores
     */
    public static function getAvailableColors(): array
    {
        return [
            '#3498db' => 'Azul',
            '#e74c3c' => 'Vermelho',
            '#2ecc71' => 'Verde',
            '#f39c12' => 'Laranja',
            '#9b59b6' => 'Roxo',
            '#1abc9c' => 'Turquesa',
            '#34495e' => 'Cinza Escuro',
            '#e91e63' => 'Rosa',
        ];
    }
}
