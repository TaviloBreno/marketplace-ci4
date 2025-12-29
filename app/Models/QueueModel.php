<?php

namespace App\Models;

use App\Entities\Queue;
use CodeIgniter\Model;

class QueueModel extends Model
{
    protected $table            = 'queues';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Queue::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sector_id',
        'name',
        'total_seats',
        'position_x',
        'position_y',
        'curve_angle',
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
        'sector_id'   => 'required|integer',
        'name'        => 'required|max_length[10]',
        'total_seats' => 'required|integer',
    ];

    protected $skipValidation = false;

    /**
     * Busca filas de um setor
     */
    public function findBySector(int $sectorId)
    {
        return $this->where('sector_id', $sectorId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Busca filas ativas de um setor
     */
    public function findActiveBySector(int $sectorId)
    {
        return $this->where('sector_id', $sectorId)
                    ->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Busca filas de um evento (via setor)
     */
    public function findByEvent(int $eventId)
    {
        $builder = $this->db->table('queues q');
        $builder->select('q.*, s.name as sector_name, s.color as sector_color, s.price as sector_price');
        $builder->join('sectors s', 's.id = q.sector_id');
        $builder->where('s.event_id', $eventId);
        $builder->where('q.is_active', 1);
        $builder->orderBy('s.sort_order', 'ASC');
        $builder->orderBy('q.sort_order', 'ASC');

        return $builder->get()->getResultObject();
    }
}
