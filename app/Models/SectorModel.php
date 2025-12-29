<?php

namespace App\Models;

use App\Entities\Sector;
use CodeIgniter\Model;

class SectorModel extends Model
{
    protected $table            = 'sectors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Sector::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'event_id',
        'name',
        'description',
        'color',
        'price',
        'capacity',
        'is_numbered',
        'position_x',
        'position_y',
        'width',
        'height',
        'sort_order',
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
        'event_id' => 'required|integer',
        'name'     => 'required|max_length[100]',
        'price'    => 'required|decimal',
    ];

    protected $skipValidation = false;

    /**
     * Busca setores de um evento
     */
    public function findByEvent(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Busca setores ativos de um evento
     */
    public function findActiveByEvent(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Busca setores com contagem de assentos
     */
    public function findWithSeatCounts(int $eventId)
    {
        $builder = $this->db->table('sectors s');
        $builder->select('s.*, COUNT(DISTINCT q.id) as queue_count, COUNT(DISTINCT st.id) as seat_count');
        $builder->join('queues q', 'q.sector_id = s.id', 'left');
        $builder->join('seats st', 'st.queue_id = q.id', 'left');
        $builder->where('s.event_id', $eventId);
        $builder->groupBy('s.id');
        $builder->orderBy('s.sort_order', 'ASC');

        return $builder->get()->getResultObject();
    }
}
