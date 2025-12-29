<?php

namespace App\Models;

use App\Entities\Seat;
use CodeIgniter\Model;

class SeatModel extends Model
{
    protected $table            = 'seats';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Seat::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'queue_id',
        'number',
        'label',
        'position_x',
        'position_y',
        'status',
        'is_accessible',
        'sort_order',
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
        'queue_id' => 'required|integer',
        'number'   => 'required|max_length[10]',
    ];

    protected $skipValidation = false;

    /**
     * Busca assentos de uma fila
     */
    public function findByQueue(int $queueId)
    {
        return $this->where('queue_id', $queueId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Busca assentos disponíveis de uma fila
     */
    public function findAvailableByQueue(int $queueId)
    {
        return $this->where('queue_id', $queueId)
                    ->where('status', 'available')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Busca assentos de um setor
     */
    public function findBySector(int $sectorId)
    {
        $builder = $this->db->table('seats s');
        $builder->select('s.*');
        $builder->join('queues q', 'q.id = s.queue_id');
        $builder->where('q.sector_id', $sectorId);
        $builder->orderBy('q.sort_order', 'ASC');
        $builder->orderBy('s.sort_order', 'ASC');

        return $builder->get()->getResultObject();
    }

    /**
     * Busca assentos de um evento com status de reserva para um dia específico
     */
    public function findByEventWithBookingStatus(int $eventId, int $eventDayId)
    {
        $builder = $this->db->table('seats s');
        $builder->select('s.*, q.name as queue_name, q.sector_id, sec.name as sector_name, sec.color as sector_color, sec.price');
        $builder->select('sb.id as booking_id, sb.status as booking_status, sb.user_id as booked_by');
        $builder->join('queues q', 'q.id = s.queue_id');
        $builder->join('sectors sec', 'sec.id = q.sector_id');
        $builder->join('seat_bookings sb', 'sb.seat_id = s.id AND sb.event_day_id = ' . $eventDayId . ' AND sb.status IN ("reserved", "confirmed")', 'left');
        $builder->where('sec.event_id', $eventId);
        $builder->where('s.status', 'available');
        $builder->orderBy('sec.sort_order', 'ASC');
        $builder->orderBy('q.sort_order', 'ASC');
        $builder->orderBy('s.sort_order', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Conta assentos por status
     */
    public function countByStatus(int $eventId, int $eventDayId): array
    {
        $builder = $this->db->table('seats s');
        $builder->select('
            COUNT(*) as total,
            SUM(CASE WHEN sb.id IS NULL AND s.status = "available" THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN sb.status = "reserved" THEN 1 ELSE 0 END) as reserved,
            SUM(CASE WHEN sb.status = "confirmed" THEN 1 ELSE 0 END) as sold,
            SUM(CASE WHEN s.status = "blocked" THEN 1 ELSE 0 END) as blocked
        ');
        $builder->join('queues q', 'q.id = s.queue_id');
        $builder->join('sectors sec', 'sec.id = q.sector_id');
        $builder->join('seat_bookings sb', 'sb.seat_id = s.id AND sb.event_day_id = ' . $eventDayId . ' AND sb.status IN ("reserved", "confirmed")', 'left');
        $builder->where('sec.event_id', $eventId);

        return $builder->get()->getRowArray();
    }
}
