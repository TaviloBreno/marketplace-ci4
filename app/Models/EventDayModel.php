<?php

namespace App\Models;

use App\Entities\EventDay;
use CodeIgniter\Model;

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
        'event_date',
        'start_time',
        'end_time',
        'doors_open',
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
        'event_date' => 'required|valid_date',
        'start_time' => 'required',
    ];

    protected $skipValidation = false;

    /**
     * Busca dias de um evento
     */
    public function findByEvent(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->orderBy('event_date', 'ASC')
                    ->orderBy('start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Busca dias ativos de um evento
     */
    public function findActiveByEvent(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->where('is_active', 1)
                    ->where('event_date >=', date('Y-m-d'))
                    ->orderBy('event_date', 'ASC')
                    ->orderBy('start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Busca o próximo dia de um evento
     */
    public function findNextDay(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->where('is_active', 1)
                    ->where('event_date >=', date('Y-m-d'))
                    ->orderBy('event_date', 'ASC')
                    ->orderBy('start_time', 'ASC')
                    ->first();
    }
}
