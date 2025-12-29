<?php

namespace App\Models;

use App\Entities\Order;
use CodeIgniter\Model;

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
        'payment_intent_id',
        'stripe_transfer_id',
        'customer_name',
        'customer_email',
        'customer_document',
        'customer_phone',
        'paid_at',
        'cancelled_at',
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
        'user_id'        => 'required|integer',
        'event_id'       => 'required|integer',
        'event_day_id'   => 'required|integer',
        'order_number'   => 'required|max_length[50]',
        'total'          => 'required|decimal',
        'customer_name'  => 'required|max_length[255]',
        'customer_email' => 'required|valid_email',
    ];

    protected $skipValidation = false;

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
     * Busca pedido por número
     */
    public function findByOrderNumber(string $orderNumber)
    {
        return $this->where('order_number', $orderNumber)->first();
    }

    /**
     * Busca pedidos com detalhes do evento
     */
    public function findWithEventDetails(int $userId)
    {
        $builder = $this->db->table('orders o');
        $builder->select('o.*, e.title as event_title, e.slug as event_slug, e.image as event_image');
        $builder->select('ed.date as event_date, ed.start_time');
        $builder->join('events e', 'e.id = o.event_id');
        $builder->join('event_days ed', 'ed.id = o.event_day_id');
        $builder->where('o.user_id', $userId);
        $builder->orderBy('o.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Calcula estatísticas de vendas para um organizador
     */
    public function getOrganizerStats(int $userId): array
    {
        $builder = $this->db->table('orders o');
        $builder->select('
            COUNT(DISTINCT o.id) as total_orders,
            SUM(CASE WHEN o.status = "paid" THEN 1 ELSE 0 END) as paid_orders,
            SUM(CASE WHEN o.status = "paid" THEN o.total ELSE 0 END) as total_revenue,
            SUM(CASE WHEN o.status = "paid" AND DATE(o.paid_at) = CURDATE() THEN o.total ELSE 0 END) as today_revenue,
            (SELECT COUNT(*) FROM tickets t 
             INNER JOIN orders o2 ON o2.id = t.order_id 
             INNER JOIN events e2 ON e2.id = o2.event_id 
             WHERE e2.user_id = e.user_id AND o2.status = "paid") as tickets_sold,
            (SELECT COUNT(DISTINCT e3.id) FROM events e3 WHERE e3.user_id = e.user_id AND e3.status = "published") as active_events
        ');
        $builder->join('events e', 'e.id = o.event_id');
        $builder->where('e.user_id', $userId);

        $result = $builder->get()->getRowArray();
        
        // Se não houver pedidos, ainda assim precisamos das estatísticas de eventos
        if (empty($result['total_orders'])) {
            $eventBuilder = $this->db->table('events');
            $eventBuilder->select('
                COUNT(CASE WHEN status = "published" THEN 1 END) as active_events
            ');
            $eventBuilder->where('user_id', $userId);
            $eventResult = $eventBuilder->get()->getRowArray();
            
            return [
                'total_orders'   => 0,
                'paid_orders'    => 0,
                'total_revenue'  => 0,
                'today_revenue'  => 0,
                'tickets_sold'   => 0,
                'active_events'  => $eventResult['active_events'] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Calcula estatísticas de um evento
     */
    public function getEventStats(int $eventId): array
    {
        $builder = $this->db->table('orders o');
        $builder->select('
            COUNT(DISTINCT o.id) as total_orders,
            SUM(CASE WHEN o.status = "paid" THEN 1 ELSE 0 END) as paid_orders,
            SUM(CASE WHEN o.status = "paid" THEN o.total ELSE 0 END) as total_revenue,
            SUM(CASE WHEN o.status = "pending" OR o.status = "processing" THEN 1 ELSE 0 END) as pending_orders,
            (SELECT COUNT(*) FROM tickets t INNER JOIN orders o2 ON o2.id = t.order_id WHERE o2.event_id = o.event_id AND o2.status = "paid") as tickets_sold
        ');
        $builder->where('o.event_id', $eventId);

        return $builder->get()->getRowArray();
    }
}
