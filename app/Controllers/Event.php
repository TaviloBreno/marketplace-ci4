<?php

namespace App\Controllers;

use App\Services\EventStoreService;
use App\Services\SeatRenderService;

class Event extends BaseController
{
    protected EventStoreService $eventService;
    protected SeatRenderService $seatRenderService;

    public function __construct()
    {
        $this->eventService = new EventStoreService();
        $this->seatRenderService = new SeatRenderService();
    }

    /**
     * Lista eventos do organizador
     */
    public function index()
    {
        $eventModel = model('EventModel');
        $events = $eventModel->findByOrganizer(auth()->id());
        $eventCounts = $eventModel->countByStatus(auth()->id());

        return view('events/index', [
            'events'      => $events,
            'eventCounts' => $eventCounts,
        ]);
    }

    /**
     * Formulário de criação de evento
     */
    public function create()
    {
        return view('events/form', [
            'event'    => null,
            'days'     => [],
            'sectors'  => [],
            'isEdit'   => false,
        ]);
    }

    /**
     * Salva novo evento
     */
    public function store()
    {
        $rules = [
            'title'         => 'required|min_length[3]|max_length[255]',
            'description'   => 'permit_empty',
            'venue_name'    => 'required|max_length[255]',
            'venue_address' => 'required|max_length[255]',
            'venue_city'    => 'required|max_length[100]',
            'venue_state'   => 'required|max_length[2]',
            'venue_zip_code'=> 'required|max_length[10]',
            'category'      => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $eventData = [
            'user_id'       => auth()->id(),
            'title'         => $this->request->getPost('title'),
            'description'   => $this->request->getPost('description'),
            'venue_name'    => $this->request->getPost('venue_name'),
            'venue_address' => $this->request->getPost('venue_address'),
            'venue_city'    => $this->request->getPost('venue_city'),
            'venue_state'   => $this->request->getPost('venue_state'),
            'venue_zip_code'=> $this->request->getPost('venue_zip_code'),
            'category'      => $this->request->getPost('category'),
            'status'        => 'draft',
        ];

        // Processar dias
        $days = $this->processDaysInput();

        // Processar setores
        $sectors = $this->processSectorsInput();

        $result = $this->eventService->createEvent($eventData, $days, $sectors);

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['error']);
        }

        // Upload de imagem
        $image = $this->request->getFile('image');
        if ($image && $image->isValid()) {
            $this->eventService->uploadImage($result['event_id'], $image, 'image');
        }

        $banner = $this->request->getFile('banner');
        if ($banner && $banner->isValid()) {
            $this->eventService->uploadImage($result['event_id'], $banner, 'banner');
        }

        return redirect()->to('organizer/events/' . $result['event_id'])
                         ->with('success', 'Evento criado com sucesso!');
    }

    /**
     * Exibe detalhes do evento
     */
    public function show(int $id)
    {
        $eventData = $this->eventService->getEventForEdit($id);

        if (!$eventData || $eventData['event']->user_id !== auth()->id()) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        $eventDayModel = model('EventDayModel');
        $nextDay = $eventDayModel->findNextDay($id);

        $orderModel = model('OrderModel');
        $stats = $orderModel->getEventStats($id);

        return view('events/show', [
            'event'   => $eventData['event'],
            'days'    => $eventData['days'],
            'sectors' => $eventData['sectors'],
            'nextDay' => $nextDay,
            'stats'   => $stats,
        ]);
    }

    /**
     * Formulário de edição
     */
    public function edit(int $id)
    {
        $eventData = $this->eventService->getEventForEdit($id);

        if (!$eventData || $eventData['event']->user_id !== auth()->id()) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        return view('events/form', [
            'event'   => $eventData['event'],
            'days'    => $eventData['days'],
            'sectors' => $eventData['sectors'],
            'isEdit'  => true,
        ]);
    }

    /**
     * Atualiza evento
     */
    public function update(int $id)
    {
        $eventModel = model('EventModel');
        $event = $eventModel->find($id);

        if (!$event || $event->user_id !== auth()->id()) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        $rules = [
            'title'         => 'required|min_length[3]|max_length[255]',
            'venue_name'    => 'required|max_length[255]',
            'venue_address' => 'required|max_length[255]',
            'venue_city'    => 'required|max_length[100]',
            'venue_state'   => 'required|max_length[2]',
            'venue_zip_code'=> 'required|max_length[10]',
            'category'      => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $eventData = [
            'title'         => $this->request->getPost('title'),
            'description'   => $this->request->getPost('description'),
            'venue_name'    => $this->request->getPost('venue_name'),
            'venue_address' => $this->request->getPost('venue_address'),
            'venue_city'    => $this->request->getPost('venue_city'),
            'venue_state'   => $this->request->getPost('venue_state'),
            'venue_zip_code'=> $this->request->getPost('venue_zip_code'),
            'category'      => $this->request->getPost('category'),
        ];

        $result = $this->eventService->updateEvent($id, $eventData);

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['error']);
        }

        // Upload de imagem
        $image = $this->request->getFile('image');
        if ($image && $image->isValid()) {
            $this->eventService->uploadImage($id, $image, 'image');
        }

        return redirect()->to('organizer/events/' . $id)->with('success', 'Evento atualizado com sucesso!');
    }

    /**
     * Publica evento
     */
    public function publish(int $id)
    {
        $eventModel = model('EventModel');
        $event = $eventModel->find($id);

        if (!$event || $event->user_id !== auth()->id()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Evento não encontrado.']);
        }

        $result = $this->eventService->publishEvent($id);

        return $this->response->setJSON($result);
    }

    /**
     * Cancela evento
     */
    public function cancel(int $id)
    {
        $eventModel = model('EventModel');
        $event = $eventModel->find($id);

        if (!$event || $event->user_id !== auth()->id()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Evento não encontrado.']);
        }

        $result = $this->eventService->cancelEvent($id);

        return $this->response->setJSON($result);
    }

    /**
     * Exibe o mapa de assentos
     */
    public function seatMap(int $id)
    {
        $eventModel = model('EventModel');
        $event = $eventModel->find($id);

        if (!$event || $event->user_id !== auth()->id()) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        $eventDayModel = model('EventDayModel');
        $days = $eventDayModel->findByEvent($id);
        $selectedDay = $this->request->getGet('day') ?? ($days[0]->id ?? null);

        $seatMapHtml = '';
        $layoutJson = '{}';

        if ($selectedDay) {
            $seatMapHtml = $this->seatRenderService->generateSeatMapHtml($id, (int) $selectedDay);
            $layoutJson = $this->seatRenderService->getLayoutJson($id, (int) $selectedDay);
        }

        return view('events/seat_map', [
            'event'       => $event,
            'days'        => $days,
            'selectedDay' => $selectedDay,
            'seatMapHtml' => $seatMapHtml,
            'layoutJson'  => $layoutJson,
        ]);
    }

    /**
     * API: Retorna layout em JSON
     */
    public function getLayout(int $id)
    {
        $eventModel = model('EventModel');
        $event = $eventModel->find($id);

        if (!$event) {
            return $this->response->setJSON(['success' => false, 'error' => 'Evento não encontrado.']);
        }

        $dayId = $this->request->getGet('day');
        $layout = $this->seatRenderService->renderEventLayout($id, $dayId ? (int) $dayId : null);

        return $this->response->setJSON([
            'success' => true,
            'layout'  => $layout,
        ]);
    }

    /**
     * Processa input de dias do formulário
     */
    protected function processDaysInput(): array
    {
        $days = [];
        $datesInput = $this->request->getPost('event_dates') ?? [];
        $timesInput = $this->request->getPost('event_times') ?? [];
        $doorsInput = $this->request->getPost('doors_open') ?? [];

        foreach ($datesInput as $index => $date) {
            if (!empty($date)) {
                $days[] = [
                    'event_date' => $date,
                    'start_time' => $timesInput[$index] ?? '19:00',
                    'doors_open' => $doorsInput[$index] ?? null,
                    'is_active'  => 1,
                ];
            }
        }

        return $days;
    }

    /**
     * Processa input de setores do formulário
     */
    protected function processSectorsInput(): array
    {
        $sectors = [];
        $sectorsInput = $this->request->getPost('sectors') ?? [];

        foreach ($sectorsInput as $sectorData) {
            if (!empty($sectorData['name'])) {
                $queues = [];
                
                if (!empty($sectorData['queues'])) {
                    foreach ($sectorData['queues'] as $queueData) {
                        if (!empty($queueData['name'])) {
                            $queues[] = [
                                'name'        => $queueData['name'],
                                'total_seats' => (int) ($queueData['total_seats'] ?? 10),
                            ];
                        }
                    }
                }

                $sectors[] = [
                    'name'        => $sectorData['name'],
                    'price'       => (float) ($sectorData['price'] ?? 0),
                    'color'       => $sectorData['color'] ?? '#3498db',
                    'is_numbered' => (int) ($sectorData['is_numbered'] ?? 1),
                    'capacity'    => (int) ($sectorData['capacity'] ?? 0),
                    'queues'      => $queues,
                ];
            }
        }

        return $sectors;
    }
}
