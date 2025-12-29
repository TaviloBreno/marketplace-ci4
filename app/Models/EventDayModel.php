<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\EventDay;

class EventDayModel extends Model
{
    protected $table            = 'event_days';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = EventDay::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'event_id',
        'date',
        'start_time',
        'end_time',
        'doors_open_time',
        'is_active',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'event_id'   => 'required|integer',
        'date'       => 'required|valid_date',
        'start_time' => 'required',
    ];

    protected $skipValidation = false;

    /**
     * Busca dias de um evento
     */
    public function findByEvent(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->orderBy('date', 'ASC')
                    ->orderBy('start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Busca próximos dias de eventos
     */
    public function findUpcoming(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->where('date >=', date('Y-m-d'))
                    ->where('is_active', 1)
                    ->orderBy('date', 'ASC')
                    ->orderBy('start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Busca o próximo dia de um evento
     */
    public function findNextDay(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->where('date >=', date('Y-m-d'))
                    ->where('is_active', 1)
                    ->orderBy('date', 'ASC')
                    ->orderBy('start_time', 'ASC')
                    ->first();
    }

    /**
     * Verifica se o evento tem dias futuros
     */
    public function hasUpcomingDays(int $eventId): bool
    {
        return $this->where('event_id', $eventId)
                    ->where('date >=', date('Y-m-d'))
                    ->where('is_active', 1)
                    ->countAllResults() > 0;
    }

    /**
     * Deleta dias de um evento
     */
    public function deleteByEvent(int $eventId): bool
    {
        return $this->where('event_id', $eventId)->delete();
    }
}
