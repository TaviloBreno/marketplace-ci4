<?php

namespace App\Models;

use App\Entities\Ticket;
use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table            = 'tickets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Ticket::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'order_id',
        'seat_booking_id',
        'ticket_code',
        'qr_code',
        'holder_name',
        'holder_document',
        'status',
        'used_at',
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
        'order_id'        => 'required|integer',
        'seat_booking_id' => 'required|integer',
        'ticket_code'     => 'required|max_length[100]',
    ];

    protected $skipValidation = false;

    /**
     * Busca tickets de um pedido
     */
    public function findByOrder(int $orderId)
    {
        return $this->where('order_id', $orderId)
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    /**
     * Busca ticket por código
     */
    public function findByCode(string $ticketCode)
    {
        return $this->where('ticket_code', $ticketCode)->first();
    }

    /**
     * Busca tickets com detalhes completos
     */
    public function findWithDetails(int $orderId)
    {
        $builder = $this->db->table('tickets t');
        $builder->select('t.*, sb.price');
        $builder->select('s.number as seat_number, s.label as seat_label');
        $builder->select('q.name as queue_name');
        $builder->select('sec.name as sector_name, sec.color as sector_color');
        $builder->join('seat_bookings sb', 'sb.id = t.seat_booking_id');
        $builder->join('seats s', 's.id = sb.seat_id');
        $builder->join('queues q', 'q.id = s.queue_id');
        $builder->join('sectors sec', 'sec.id = q.sector_id');
        $builder->where('t.order_id', $orderId);
        $builder->orderBy('t.id', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Marca ticket como usado
     */
    public function markAsUsed(int $ticketId): bool
    {
        return $this->update($ticketId, [
            'status'  => 'used',
            'used_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Valida um ticket para check-in
     */
    public function validateForCheckIn(string $ticketCode, int $eventId)
    {
        $builder = $this->db->table('tickets t');
        $builder->select('t.*, o.event_id, o.event_day_id, e.title as event_title');
        $builder->select('ed.date as event_date, ed.start_time');
        $builder->select('s.number as seat_number, sec.name as sector_name');
        $builder->join('orders o', 'o.id = t.order_id');
        $builder->join('events e', 'e.id = o.event_id');
        $builder->join('event_days ed', 'ed.id = o.event_day_id');
        $builder->join('seat_bookings sb', 'sb.id = t.seat_booking_id');
        $builder->join('seats s', 's.id = sb.seat_id');
        $builder->join('queues q', 'q.id = s.queue_id');
        $builder->join('sectors sec', 'sec.id = q.sector_id');
        $builder->where('t.ticket_code', $ticketCode);
        $builder->where('o.event_id', $eventId);

        return $builder->get()->getRowArray();
    }
}
