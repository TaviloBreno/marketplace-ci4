<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\SeatBooking;

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
        'order_id',
        'status',
        'price',
        'reserved_at',
        'expires_at',
        'confirmed_at',
        'session_id',
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
        'reserved_at'  => 'required',
    ];

    protected $skipValidation = false;

    /**
     * Cria uma reserva temporária
     */
    public function createReservation(int $seatId, int $eventDayId, float $price, string $sessionId, int $expirationMinutes = 15): ?int
    {
        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expirationMinutes} minutes"));
        
        $data = [
            'seat_id'      => $seatId,
            'event_day_id' => $eventDayId,
            'price'        => $price,
            'session_id'   => $sessionId,
            'status'       => 'reserved',
            'reserved_at'  => $now,
            'expires_at'   => $expiresAt,
        ];
        
        if ($this->insert($data)) {
            return $this->getInsertID();
        }
        
        return null;
    }

    /**
     * Confirma uma reserva
     */
    public function confirmReservation(int $bookingId, int $userId, int $orderId): bool
    {
        return $this->update($bookingId, [
            'user_id'      => $userId,
            'order_id'     => $orderId,
            'status'       => 'confirmed',
            'confirmed_at' => date('Y-m-d H:i:s'),
            'expires_at'   => null,
        ]);
    }

    /**
     * Cancela uma reserva
     */
    public function cancelReservation(int $bookingId): bool
    {
        return $this->update($bookingId, [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Busca reservas expiradas
     */
    public function findExpired()
    {
        return $this->where('status', 'reserved')
                    ->where('expires_at <', date('Y-m-d H:i:s'))
                    ->findAll();
    }

    /**
     * Expira reservas antigas
     */
    public function expireOldReservations(): int
    {
        $expired = $this->findExpired();
        
        if (empty($expired)) {
            return 0;
        }
        
        $expiredIds = array_column($expired, 'id');
        $seatIds = array_column($expired, 'seat_id');
        
        // Atualiza status das reservas
        $this->whereIn('id', $expiredIds)
             ->set(['status' => 'expired'])
             ->update();
        
        // Libera os assentos
        $seatModel = model('SeatModel');
        $seatModel->releaseSeats($seatIds);
        
        return count($expiredIds);
    }

    /**
     * Busca reservas por sessão
     */
    public function findBySession(string $sessionId)
    {
        return $this->where('session_id', $sessionId)
                    ->where('status', 'reserved')
                    ->findAll();
    }

    /**
     * Busca reservas de um pedido
     */
    public function findByOrder(int $orderId)
    {
        return $this->where('order_id', $orderId)
                    ->findAll();
    }

    /**
     * Busca reservas de um usuário
     */
    public function findByUser(int $userId)
    {
        return $this->where('user_id', $userId)
                    ->where('status', 'confirmed')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Verifica se o assento está reservado para um dia específico
     */
    public function isSeatReserved(int $seatId, int $eventDayId): bool
    {
        return $this->where('seat_id', $seatId)
                    ->where('event_day_id', $eventDayId)
                    ->whereIn('status', ['reserved', 'confirmed'])
                    ->countAllResults() > 0;
    }
}
