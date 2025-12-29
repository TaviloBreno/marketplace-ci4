<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Sector extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'          => 'integer',
        'event_id'    => 'integer',
        'price'       => 'float',
        'capacity'    => 'integer',
        'is_numbered' => 'boolean',
        'position_x'  => 'integer',
        'position_y'  => 'integer',
        'width'       => 'integer',
        'height'      => 'integer',
        'sort_order'  => 'integer',
        'is_active'   => 'boolean',
    ];

    /**
     * Retorna o preço formatado em BRL
     */
    public function getFormattedPrice(): string
    {
        return 'R$ ' . number_format($this->attributes['price'], 2, ',', '.');
    }

    /**
     * Retorna o estilo CSS do setor
     */
    public function getStyle(): string
    {
        return sprintf(
            'background-color: %s; left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
            $this->attributes['color'],
            $this->attributes['position_x'],
            $this->attributes['position_y'],
            $this->attributes['width'],
            $this->attributes['height']
        );
    }
}
