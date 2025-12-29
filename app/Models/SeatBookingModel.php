<?php

namespace App\Models;

use App\Entities\SeatBooking;
use CodeIgniter\Model;

class SeatBookingModel extends Model
{
    protected $table            = 'seat_bookings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = SeatBooking::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'seat_id',
        'event_day_id',
        'user_id',
        'session_id',
        'status',
        'price',
        'reserved_at',
        'expires_at',
        'confirmed_at',
        'payment_intent_id',
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
        'seat_id'      => 'required|integer',
        'event_day_id' => 'required|integer',
        'price'        => 'required|decimal',
    ];

    protected $skipValidation = false;

    /**
     * Tempo de expiração da reserva em minutos
     */
    public const RESERVATION_TIMEOUT = 15;

    /**
     * Verifica se um assento está disponível para um dia específico
     */
    public function isSeatAvailable(int $seatId, int $eventDayId): bool
    {
        return $this->where('seat_id', $seatId)
                    ->where('event_day_id', $eventDayId)
                    ->whereIn('status', ['reserved', 'confirmed'])
                    ->where('(status != "reserved" OR expires_at > NOW())')
                    ->countAllResults() === 0;
    }

    /**
     * Reserva um assento
     */
    public function reserveSeat(int $seatId, int $eventDayId, float $price, int $userId = null, string $sessionId = null): ?int
    {
        if (!$this->isSeatAvailable($seatId, $eventDayId)) {
            return null;
        }

        $data = [
            'seat_id'      => $seatId,
            'event_day_id' => $eventDayId,
            'user_id'      => $userId,
            'session_id'   => $sessionId,
            'status'       => 'reserved',
            'price'        => $price,
            'reserved_at'  => date('Y-m-d H:i:s'),
            'expires_at'   => date('Y-m-d H:i:s', strtotime('+' . self::RESERVATION_TIMEOUT . ' minutes')),
        ];

        $this->insert($data);
        return $this->getInsertID();
    }

    /**
     * Confirma uma reserva
     */
    public function confirmBooking(int $bookingId, string $paymentIntentId = null): bool
    {
        return $this->update($bookingId, [
            'status'            => 'confirmed',
            'confirmed_at'      => date('Y-m-d H:i:s'),
            'payment_intent_id' => $paymentIntentId,
        ]);
    }

    /**
     * Cancela uma reserva
     */
    public function cancelBooking(int $bookingId): bool
    {
        return $this->update($bookingId, [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Expira reservas antigas
     */
    public function expireOldReservations(): int
    {
        return $this->where('status', 'reserved')
                    ->where('expires_at <', date('Y-m-d H:i:s'))
                    ->set(['status' => 'expired'])
                    ->update();
    }

    /**
     * Busca reservas de um usuário
     */
    public function findByUser(int $userId)
    {
        return $this->where('user_id', $userId)
                    ->whereIn('status', ['reserved', 'confirmed'])
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Busca reservas de uma sessão
     */
    public function findBySession(string $sessionId)
    {
        return $this->where('session_id', $sessionId)
                    ->where('status', 'reserved')
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->findAll();
    }

    /**
     * Busca reservas com detalhes do assento
     */
    public function findWithSeatDetails(int $userId = null, string $sessionId = null)
    {
        $builder = $this->db->table('seat_bookings sb');
        $builder->select('sb.*, s.number as seat_number, s.label as seat_label');
        $builder->select('q.name as queue_name');
        $builder->select('sec.name as sector_name, sec.color as sector_color');
        $builder->select('e.title as event_title, e.slug as event_slug');
        $builder->select('ed.date as event_date, ed.start_time');
        $builder->join('seats s', 's.id = sb.seat_id');
        $builder->join('queues q', 'q.id = s.queue_id');
        $builder->join('sectors sec', 'sec.id = q.sector_id');
        $builder->join('events e', 'e.id = sec.event_id');
        $builder->join('event_days ed', 'ed.id = sb.event_day_id');
        
        if ($userId) {
            $builder->where('sb.user_id', $userId);
        }
        
        if ($sessionId) {
            $builder->where('sb.session_id', $sessionId);
        }
        
        $builder->whereIn('sb.status', ['reserved', 'confirmed']);
        $builder->orderBy('sb.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }
}
