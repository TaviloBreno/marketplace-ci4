<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Row extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'           => 'integer',
        'sector_id'    => 'integer',
        'seats_count'  => 'integer',
        'row_number'   => 'integer',
        'curve_offset' => 'integer',
    ];

    protected $seats;

    /**
     * Gera o label da fila (A, B, C, ..., AA, AB, ...)
     */
    public static function generateLabel(int $rowNumber): string
    {
        $label = '';
        while ($rowNumber >= 0) {
            $label = chr(65 + ($rowNumber % 26)) . $label;
            $rowNumber = intval($rowNumber / 26) - 1;
        }
        return $label;
    }

    /**
     * Retorna o label formatado
     */
    public function getLabel(): string
    {
        return $this->attributes['name'] ?? self::generateLabel($this->attributes['row_number']);
    }
}
