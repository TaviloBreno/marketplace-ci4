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
     * Retorna o horário de abertura dos portões formatado
     */
    public function getFormattedDoorsOpenTime(): ?string
    {
        if (empty($this->attributes['doors_open_time'])) {
            return null;
        }
        return substr($this->attributes['doors_open_time'], 0, 5);
    }

    /**
     * Retorna data e hora completa formatada
     */
    public function getFullDateTime(): string
    {
        $date = new \DateTime($this->attributes['date']);
        $formatted = $date->format('d/m/Y') . ' às ' . $this->getFormattedStartTime();
        
        if ($endTime = $this->getFormattedEndTime()) {
            $formatted .= ' - ' . $endTime;
        }
        
        return $formatted;
    }

    /**
     * Verifica se o dia já passou
     */
    public function isPast(): bool
    {
        $eventDate = new \DateTime($this->attributes['date'] . ' ' . $this->attributes['start_time']);
        $now = new \DateTime();
        return $eventDate < $now;
    }

    /**
     * Verifica se é hoje
     */
    public function isToday(): bool
    {
        $eventDate = new \DateTime($this->attributes['date']);
        $today = new \DateTime('today');
        return $eventDate->format('Y-m-d') === $today->format('Y-m-d');
    }
}
