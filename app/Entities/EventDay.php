<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class EventDay extends Entity
{
    protected $datamap = [];
    protected $dates   = ['date', 'created_at', 'updated_at'];
    protected $casts   = [
        'id'        => 'integer',
        'event_id'  => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Retorna a data formatada
     */
    public function getFormattedDate(): string
    {
        $date = new \DateTime($this->attributes['date']);
        return $date->format('d/m/Y');
    }

    /**
     * Retorna o horário de início formatado
     */
    public function getFormattedStartTime(): string
    {
        return substr($this->attributes['start_time'], 0, 5);
    }

    /**
     * Retorna o horário de término formatado
     */
    public function getFormattedEndTime(): ?string
    {
        if (empty($this->attributes['end_time'])) {
            return null;
        }
        return substr($this->attributes['end_time'], 0, 5);
    }

    /**
     * Retorna a abertura dos portões formatada
     */
    public function getFormattedDoorsOpen(): ?string
    {
        if (empty($this->attributes['doors_open'])) {
            return null;
        }
        return substr($this->attributes['doors_open'], 0, 5);
    }

    /**
     * Retorna data e hora formatados
     */
    public function getDateTimeLabel(): string
    {
        return $this->getFormattedDate() . ' às ' . $this->getFormattedStartTime();
    }
}
