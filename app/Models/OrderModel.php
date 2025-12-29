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
            COUNT(*) as total_orders,
            SUM(CASE WHEN o.status = "paid" THEN 1 ELSE 0 END) as paid_orders,
            SUM(CASE WHEN o.status = "paid" THEN o.total ELSE 0 END) as total_revenue,
            SUM(CASE WHEN o.status = "paid" AND DATE(o.paid_at) = CURDATE() THEN o.total ELSE 0 END) as today_revenue
        ');
        $builder->join('events e', 'e.id = o.event_id');
        $builder->where('e.user_id', $userId);

        return $builder->get()->getRowArray();
    }

    /**
     * Calcula estatísticas de um evento
     */
    public function getEventStats(int $eventId): array
    {
        $builder = $this->db->table('orders o');
        $builder->select('
            COUNT(*) as total_orders,
            SUM(CASE WHEN o.status = "paid" THEN 1 ELSE 0 END) as paid_orders,
            SUM(CASE WHEN o.status = "paid" THEN o.total ELSE 0 END) as total_revenue
        ');
        $builder->where('o.event_id', $eventId);

        return $builder->get()->getRowArray();
    }
}
