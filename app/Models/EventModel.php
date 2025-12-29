<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Event;

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
        'venue_zipcode',
        'category',
        'status',
        'is_featured',
        'has_seat_map',
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
        'venue_name'    => 'required|min_length[3]|max_length[255]',
        'venue_address' => 'required',
        'venue_city'    => 'required|max_length[100]',
        'venue_state'   => 'required|exact_length[2]',
        'venue_zipcode' => 'required|max_length[10]',
        'category'      => 'required|in_list[show,theater,sports,festival,conference,workshop,party,exhibition,other]',
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
    public function findPublished(int $limit = 0)
    {
        $builder = $this->where('status', 'published')
                        ->orderBy('created_at', 'DESC');
        
        if ($limit > 0) {
            $builder->limit($limit);
        }
        
        return $builder->findAll();
    }

    /**
     * Busca eventos em destaque
     */
    public function findFeatured(int $limit = 6)
    {
        return $this->where('status', 'published')
                    ->where('is_featured', 1)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
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
    public function findByCategory(string $category, int $limit = 0)
    {
        $builder = $this->where('status', 'published')
                        ->where('category', $category)
                        ->orderBy('created_at', 'DESC');
        
        if ($limit > 0) {
            $builder->limit($limit);
        }
        
        return $builder->findAll();
    }

    /**
     * Busca próximos eventos
     */
    public function findUpcoming(int $limit = 10)
    {
        return $this->select('events.*')
                    ->join('event_days', 'event_days.event_id = events.id')
                    ->where('events.status', 'published')
                    ->where('event_days.date >=', date('Y-m-d'))
                    ->groupBy('events.id')
                    ->orderBy('event_days.date', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Pesquisa eventos
     */
    public function search(string $term, int $limit = 20)
    {
        return $this->like('title', $term)
                    ->orLike('description', $term)
                    ->orLike('venue_name', $term)
                    ->orLike('venue_city', $term)
                    ->where('status', 'published')
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
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
        
        $results = $builder->select('status, COUNT(*) as total')
                          ->groupBy('status')
                          ->get()
                          ->getResultArray();
        
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
     * Carrega o evento com relacionamentos
     */
    public function findWithRelations(int $id)
    {
        $event = $this->find($id);
        
        if (!$event) {
            return null;
        }
        
        // Carrega dias do evento
        $eventDayModel = model('EventDayModel');
        $event->days = $eventDayModel->findByEvent($id);
        
        // Carrega setores
        $sectorModel = model('SectorModel');
        $event->sectors = $sectorModel->findByEvent($id);
        
        return $event;
    }
}
