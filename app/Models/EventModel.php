<?php

namespace App\Models;

use App\Entities\Event;
use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table            = 'events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Event::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'title',
        'slug',
        'description',
        'image',
        'banner',
        'venue_name',
        'venue_address',
        'venue_city',
        'venue_state',
        'venue_zip_code',
        'category',
        'status',
        'is_featured',
        'max_tickets_per_purchase',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'user_id'       => 'required|integer',
        'title'         => 'required|min_length[3]|max_length[255]',
        'venue_name'    => 'required|max_length[255]',
        'venue_address' => 'required|max_length[255]',
        'venue_city'    => 'required|max_length[100]',
        'venue_state'   => 'required|max_length[2]',
        'venue_zip_code'=> 'required|max_length[10]',
        'category'      => 'required|max_length[50]',
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'O título do evento é obrigatório.',
            'min_length' => 'O título deve ter pelo menos 3 caracteres.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Busca eventos por organizador
     */
    public function findByOrganizer(int $userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Busca eventos publicados
     */
    public function findPublished(int $limit = 10, int $offset = 0)
    {
        return $this->where('status', 'published')
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit, $offset);
    }

    /**
     * Busca eventos em destaque
     */
    public function findFeatured(int $limit = 6)
    {
        return $this->where('status', 'published')
                    ->where('is_featured', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit);
    }

    /**
     * Busca evento por slug
     */
    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Busca eventos por categoria
     */
    public function findByCategory(string $category, int $limit = 10)
    {
        return $this->where('status', 'published')
                    ->where('category', $category)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit);
    }

    /**
     * Busca eventos com próximas datas
     */
    public function findUpcoming(int $limit = 10)
    {
        $builder = $this->db->table('events e');
        $builder->select('e.*, MIN(ed.date) as next_date');
        $builder->join('event_days ed', 'ed.event_id = e.id');
        $builder->where('e.status', 'published');
        $builder->where('ed.date >=', date('Y-m-d'));
        $builder->where('ed.is_active', 1);
        $builder->groupBy('e.id');
        $builder->orderBy('next_date', 'ASC');
        $builder->limit($limit);

        return $builder->get()->getResultObject();
    }

    /**
     * Conta eventos por status
     */
    public function countByStatus(int $userId = null): array
    {
        $builder = $this->builder();
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }

        $builder->select('status, COUNT(*) as total');
        $builder->groupBy('status');

        $results = $builder->get()->getResultArray();
        
        $counts = [
            'draft'     => 0,
            'published' => 0,
            'cancelled' => 0,
            'finished'  => 0,
        ];

        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Gera slug único
     */
    public function generateUniqueSlug(string $title, int $excludeId = null): string
    {
        $slug = url_title($title, '-', true);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $builder = $this->builder();
            $builder->where('slug', $slug);
            
            if ($excludeId) {
                $builder->where('id !=', $excludeId);
            }

            if ($builder->countAllResults() === 0) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
