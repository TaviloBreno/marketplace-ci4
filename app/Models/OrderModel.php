<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Order;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Order::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'event_id',
        'event_day_id',
        'order_number',
        'subtotal',
        'service_fee',
        'discount',
        'total',
        'status',
        'payment_method',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'paid_at',
        'cancelled_at',
        'refunded_at',
        'notes',
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
        'user_id'      => 'required|integer',
        'event_id'     => 'required|integer',
        'event_day_id' => 'required|integer',
        'order_number' => 'required|max_length[50]',
        'subtotal'     => 'required|decimal',
        'total'        => 'required|decimal',
    ];

    protected $skipValidation = false;

    /**
     * Busca pedido por número
     */
    public function findByOrderNumber(string $orderNumber)
    {
        return $this->where('order_number', $orderNumber)->first();
    }

    /**
     * Busca pedidos de um usuário
     */
    public function findByUser(int $userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Busca pedidos de um evento
     */
    public function findByEvent(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Busca pedidos pagos de um evento
     */
    public function findPaidByEvent(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->where('status', 'paid')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Marca pedido como pago
     */
    public function markAsPaid(int $orderId, string $chargeId = null): bool
    {
        $data = [
            'status'  => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($chargeId) {
            $data['stripe_charge_id'] = $chargeId;
        }
        
        return $this->update($orderId, $data);
    }

    /**
     * Cancela pedido
     */
    public function cancel(int $orderId): bool
    {
        return $this->update($orderId, [
            'status'       => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Reembolsa pedido
     */
    public function refund(int $orderId): bool
    {
        return $this->update($orderId, [
            'status'      => 'refunded',
            'refunded_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Calcula total de vendas de um evento
     */
    public function getTotalSales(int $eventId): float
    {
        $result = $this->selectSum('total')
                       ->where('event_id', $eventId)
                       ->where('status', 'paid')
                       ->first();
        
        return (float) ($result->total ?? 0);
    }

    /**
     * Conta pedidos por status
     */
    public function countByStatus(int $eventId = null): array
    {
        $builder = $this->builder();
        
        if ($eventId) {
            $builder->where('event_id', $eventId);
        }
        
        $results = $builder->select('status, COUNT(*) as total')
                          ->groupBy('status')
                          ->get()
                          ->getResultArray();
        
        $counts = [
            'pending'    => 0,
            'processing' => 0,
            'paid'       => 0,
            'cancelled'  => 0,
            'refunded'   => 0,
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        
        return $counts;
    }

    /**
     * Busca pedidos recentes
     */
    public function findRecent(int $limit = 10, int $userId = null)
    {
        $builder = $this->orderBy('created_at', 'DESC')->limit($limit);
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }
        
        return $builder->findAll();
    }
}
