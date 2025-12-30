<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\EventDayModel;
use App\Models\SectorModel;
use App\Models\QueueModel;
use App\Models\SeatModel;
use App\Models\SeatBookingModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\BaseConnection;

class PublicController extends BaseController
{
    protected EventModel $eventModel;
    protected EventDayModel $eventDayModel;
    protected SectorModel $sectorModel;
    protected QueueModel $queueModel;
    protected SeatModel $seatModel;
    protected SeatBookingModel $seatBookingModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->eventDayModel = new EventDayModel();
        $this->sectorModel = new SectorModel();
        $this->queueModel = new QueueModel();
        $this->seatModel = new SeatModel();
        $this->seatBookingModel = new SeatBookingModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Home page - exibe eventos em destaque e categorias
     */
    public function index()
    {
        // Eventos em destaque (mais recentes publicados)
        $featuredEvents = $this->eventModel
            ->where('status', 'published')
            ->orderBy('created_at', 'DESC')
            ->limit(6)
            ->find();

        // Eventos por categoria
        $categories = ['show', 'teatro', 'esporte', 'festival', 'conferencia'];
        $eventsByCategory = [];
        
        foreach ($categories as $category) {
            $eventsByCategory[$category] = $this->eventModel
                ->where('status', 'published')
                ->where('category', $category)
                ->orderBy('created_at', 'DESC')
                ->limit(4)
                ->find();
        }

        // Próximos eventos (baseado na data)
        $upcomingEvents = $this->eventModel
            ->select('events.*, MIN(event_days.event_date) as next_event_date')
            ->join('event_days', 'event_days.event_id = events.id')
            ->where('events.status', 'published')
            ->where('event_days.event_date >=', date('Y-m-d'))
            ->groupBy('events.id')
            ->orderBy('next_event_date', 'ASC')
            ->limit(8)
            ->find();

        return view('public/home', [
            'featuredEvents' => $featuredEvents,
            'eventsByCategory' => $eventsByCategory,
            'upcomingEvents' => $upcomingEvents,
            'categories' => $categories
        ]);
    }

    /**
     * Listagem de eventos com filtros
     */
    public function events()
    {
        $categoria = $this->request->getGet('categoria');
        $busca = $this->request->getGet('busca');
        $data = $this->request->getGet('data');
        $cidade = $this->request->getGet('cidade');
        $ordem = $this->request->getGet('ordem') ?? 'recentes';
        $page = $this->request->getGet('page') ?? 1;

        $builder = $this->eventModel->where('status', 'published');

        // Filtro por categoria
        if ($categoria) {
            $builder->where('category', $categoria);
        }

        // Filtro por busca
        if ($busca) {
            $builder->groupStart()
                ->like('title', $busca)
                ->orLike('description', $busca)
                ->orLike('venue_name', $busca)
                ->groupEnd();
        }

        // Filtro por cidade
        if ($cidade) {
            $builder->like('venue_city', $cidade);
        }

        // Ordenação
        switch ($ordem) {
            case 'preco_menor':
                $builder->orderBy('base_price', 'ASC');
                break;
            case 'preco_maior':
                $builder->orderBy('base_price', 'DESC');
                break;
            case 'nome':
                $builder->orderBy('title', 'ASC');
                break;
            case 'recentes':
            default:
                $builder->orderBy('created_at', 'DESC');
                break;
        }

        $events = $builder->paginate(12, 'events');
        $pager = $this->eventModel->pager;

        // Cidades disponíveis para filtro
        $cities = $this->eventModel
            ->select('venue_city')
            ->where('status', 'published')
            ->where('venue_city IS NOT NULL')
            ->groupBy('venue_city')
            ->findAll();

        return view('public/events/list', [
            'events' => $events,
            'pager' => $pager,
            'cities' => array_column($cities, 'venue_city'),
            'filters' => [
                'categoria' => $categoria,
                'busca' => $busca,
                'data' => $data,
                'cidade' => $cidade,
                'ordem' => $ordem
            ]
        ]);
    }

    /**
     * Detalhes do evento
     */
    public function event(string $slug)
    {
        $event = $this->eventModel->where('slug', $slug)->first();

        if (!$event || $event->status !== 'published') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Evento não encontrado');
        }

        // Dias do evento
        $eventDays = $this->eventDayModel
            ->where('event_id', $event->id)
            ->where('event_date >=', date('Y-m-d'))
            ->orderBy('event_date', 'ASC')
            ->findAll();

        // Setores e preços
        $sectors = $this->sectorModel
            ->where('event_id', $event->id)
            ->findAll();

        // Contagem de assentos disponíveis por setor
        foreach ($sectors as &$sector) {
            // Total de assentos no setor (query limpa)
            $totalSeats = $this->db->table('seats')
                ->join('queues', 'queues.id = seats.queue_id')
                ->where('queues.sector_id', $sector->id)
                ->countAllResults();
            
            // Assentos já reservados/confirmados (query limpa)
            $bookedSeats = $this->db->table('seat_bookings')
                ->join('seats', 'seats.id = seat_bookings.seat_id')
                ->join('queues', 'queues.id = seats.queue_id')
                ->where('queues.sector_id', $sector->id)
                ->where('seat_bookings.status !=', 'cancelled')
                ->countAllResults();
            
            $sector->available_seats = $totalSeats - $bookedSeats;
            $sector->total_seats = $totalSeats;
        }

        // Eventos relacionados (mesma categoria)
        $relatedEvents = $this->eventModel
            ->where('status', 'published')
            ->where('category', $event->category)
            ->where('id !=', $event->id)
            ->limit(4)
            ->find();

        return view('public/events/detail', [
            'event' => $event,
            'eventDays' => $eventDays,
            'sectors' => $sectors,
            'relatedEvents' => $relatedEvents
        ]);
    }

    /**
     * Seleção de assentos
     */
    public function selectSeats(string $slug, int $eventDayId)
    {
        $event = $this->eventModel->where('slug', $slug)->first();

        if (!$event || $event->status !== 'published') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Evento não encontrado');
        }

        $eventDay = $this->eventDayModel->find($eventDayId);

        if (!$eventDay || $eventDay->event_id != $event->id) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data não encontrada');
        }

        // Verificar se a data ainda não passou
        if ($eventDay->event_date < date('Y-m-d')) {
            return redirect()->to("evento/{$slug}")->with('error', 'Esta data já passou.');
        }

        // Carregar layout completo
        $sectors = $this->sectorModel->where('event_id', $event->id)->findAll();
        $layout = [];

        foreach ($sectors as $sector) {
            $queues = $this->queueModel->where('sector_id', $sector->id)->findAll();
            $sectorData = [
                'sector' => $sector,
                'queues' => []
            ];

            foreach ($queues as $queue) {
                $seats = $this->seatModel->where('queue_id', $queue->id)->findAll();
                
                // Verificar status de cada assento
                foreach ($seats as &$seat) {
                    $booking = $this->seatBookingModel
                        ->where('seat_id', $seat->id)
                        ->where('event_day_id', $eventDayId)
                        ->whereIn('status', ['reserved', 'confirmed'])
                        ->first();
                    
                    $seat->is_available = $booking === null;
                    $seat->booking_status = $booking ? $booking->status : null;
                }

                $sectorData['queues'][] = [
                    'queue' => $queue,
                    'seats' => $seats
                ];
            }

            $layout[] = $sectorData;
        }

        return view('public/events/select_seats', [
            'event' => $event,
            'eventDay' => $eventDay,
            'layout' => $layout,
            'sectors' => $sectors
        ]);
    }

    /**
     * API: Retorna layout dos assentos em JSON
     */
    public function getSeatsLayout(int $eventId, int $eventDayId): ResponseInterface
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event) {
            return $this->response->setJSON(['error' => 'Evento não encontrado'])->setStatusCode(404);
        }

        $sectors = $this->sectorModel->where('event_id', $eventId)->findAll();
        $layout = [];

        foreach ($sectors as $sector) {
            $queues = $this->queueModel->where('sector_id', $sector->id)->findAll();
            $sectorSeats = [];

            foreach ($queues as $queue) {
                $seats = $this->seatModel->where('queue_id', $queue->id)->findAll();
                
                foreach ($seats as $seat) {
                    $booking = $this->seatBookingModel
                        ->where('seat_id', $seat->id)
                        ->where('event_day_id', $eventDayId)
                        ->whereIn('status', ['reserved', 'confirmed'])
                        ->first();

                    $sectorSeats[] = [
                        'id' => $seat->id,
                        'code' => $seat->code,
                        'queue' => $queue->name,
                        'position_x' => $seat->position_x,
                        'position_y' => $seat->position_y,
                        'status' => $booking ? 'occupied' : 'available',
                        'price' => (float) $sector->price
                    ];
                }
            }

            $layout[] = [
                'id' => $sector->id,
                'name' => $sector->name,
                'color' => $sector->color ?? '#6366f1',
                'price' => (float) $sector->price,
                'seats' => $sectorSeats
            ];
        }

        return $this->response->setJSON([
            'event' => [
                'id' => $event->id,
                'title' => $event->title
            ],
            'layout' => $layout
        ]);
    }

    /**
     * Busca de eventos (para autocomplete)
     */
    public function search(): ResponseInterface
    {
        $term = $this->request->getGet('q');
        
        if (strlen($term) < 2) {
            return $this->response->setJSON([]);
        }

        $events = $this->eventModel
            ->select('id, title, slug, venue_name, venue_city, image')
            ->where('status', 'published')
            ->groupStart()
                ->like('title', $term)
                ->orLike('venue_name', $term)
                ->orLike('venue_city', $term)
            ->groupEnd()
            ->limit(10)
            ->find();

        $results = array_map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'venue' => $event->venue_name,
                'city' => $event->venue_city,
                'image' => $event->image ? base_url('uploads/events/' . $event->image) : null,
                'url' => base_url('evento/' . $event->slug)
            ];
        }, $events);

        return $this->response->setJSON($results);
    }
}
