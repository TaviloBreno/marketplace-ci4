<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Queue extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'          => 'integer',
        'sector_id'   => 'integer',
        'total_seats' => 'integer',
        'position_x'  => 'integer',
        'position_y'  => 'integer',
        'curve_angle' => 'integer',
        'sort_order'  => 'integer',
        'is_active'   => 'boolean',
    ];

    /**
     * Retorna o label da fila (ex: Fila A)
     */
    public function getLabel(): string
    {
        return 'Fila ' . $this->attributes['name'];
    }
}
