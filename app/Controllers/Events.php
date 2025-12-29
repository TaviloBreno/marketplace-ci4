<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\EventStoreService;
use App\Validation\EventValidation;

class Events extends BaseController
{
    protected $eventModel;
    protected $eventStoreService;

    public function __construct()
    {
        $this->eventModel = model('EventModel');
        $this->eventStoreService = new EventStoreService();
    }

    /**
     * Lista eventos do organizador
     */
    public function index()
    {
        $user = auth()->user();
        
        $data = [
            'events'      => $this->eventModel->findByOrganizer($user->id),
            'eventCounts' => $this->eventModel->countByStatus($user->id),
        ];

        return view('organizer/events/index', $data);
    }

    /**
     * Formulário de criação de evento
     */
    public function create()
    {
        $data = [
            'categories' => \App\Entities\Event::getCategories(),
            'states'     => $this->getBrazilianStates(),
        ];

        return view('organizer/events/form', $data);
    }

    /**
     * Salva novo evento
     */
    public function store()
    {
        $validation = new EventValidation();
        
        if (!$this->validate($validation->getRules(), $validation->getMessages())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $user = auth()->user();
            $eventData = $this->getEventDataFromPost();
            $eventData['user_id'] = $user->id;

            // Processa upload de imagens
            $eventData['image'] = $this->uploadImage('image');
            $eventData['banner'] = $this->uploadImage('banner');

            // Cria o evento
            $event = $this->eventStoreService->createEvent($eventData);

            // Processa dias do evento
            $days = $this->request->getPost('days');
            if (!empty($days)) {
                $this->eventStoreService->createEventDays($event->id, $days);
            }

            // Processa setores (se for mapa de assentos)
            if ($eventData['has_seat_map'] ?? false) {
                $sectors = $this->request->getPost('sectors');
                if (!empty($sectors)) {
                    $this->eventStoreService->createSectors($event->id, $sectors);
                }
            }

            return redirect()->to("organizer/events/{$event->id}")->with('success', 'Evento criado com sucesso!');

        } catch (\Exception $e) {
            log_message('error', 'Erro ao criar evento: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao criar evento: ' . $e->getMessage());
        }
    }

    /**
     * Exibe detalhes de um evento
     */
    public function show(int $id)
    {
        $user = auth()->user();
        $event = $this->eventModel->findWithRelations($id);

        if (!$event || $event->user_id !== $user->id) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        // Estatísticas do evento
        $orderModel = model('OrderModel');
        $sectorModel = model('SectorModel');

        $data = [
            'event'       => $event,
            'totalSales'  => $orderModel->getTotalSales($id),
            'orderCounts' => $orderModel->countByStatus($id),
            'sectorStats' => $sectorModel->getStats($id),
        ];

        return view('organizer/events/show', $data);
    }

    /**
     * Formulário de edição de evento
     */
    public function edit(int $id)
    {
        $user = auth()->user();
        $event = $this->eventModel->findWithRelations($id);

        if (!$event || $event->user_id !== $user->id) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        $data = [
            'event'      => $event,
            'categories' => \App\Entities\Event::getCategories(),
            'states'     => $this->getBrazilianStates(),
        ];

        return view('organizer/events/form', $data);
    }

    /**
     * Atualiza evento
     */
    public function update(int $id)
    {
        $user = auth()->user();
        $event = $this->eventModel->find($id);

        if (!$event || $event->user_id !== $user->id) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        $validation = new EventValidation();
        
        if (!$this->validate($validation->getRules(), $validation->getMessages())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $eventData = $this->getEventDataFromPost();

            // Processa upload de imagens (se enviadas)
            if ($this->request->getFile('image')->isValid()) {
                $eventData['image'] = $this->uploadImage('image');
            }
            if ($this->request->getFile('banner')->isValid()) {
                $eventData['banner'] = $this->uploadImage('banner');
            }

            // Atualiza o evento
            $this->eventStoreService->updateEvent($id, $eventData);

            // Atualiza dias do evento
            $days = $this->request->getPost('days');
            $this->eventStoreService->updateEventDays($id, $days ?? []);

            return redirect()->to("organizer/events/{$id}")->with('success', 'Evento atualizado com sucesso!');

        } catch (\Exception $e) {
            log_message('error', 'Erro ao atualizar evento: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar evento: ' . $e->getMessage());
        }
    }

    /**
     * Publica um evento
     */
    public function publish(int $id)
    {
        $user = auth()->user();
        $event = $this->eventModel->find($id);

        if (!$event || $event->user_id !== $user->id) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        $this->eventModel->update($id, ['status' => 'published']);
        return redirect()->back()->with('success', 'Evento publicado com sucesso!');
    }

    /**
     * Cancela um evento
     */
    public function cancel(int $id)
    {
        $user = auth()->user();
        $event = $this->eventModel->find($id);

        if (!$event || $event->user_id !== $user->id) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        $this->eventModel->update($id, ['status' => 'cancelled']);
        return redirect()->back()->with('success', 'Evento cancelado.');
    }

    /**
     * Deleta um evento (soft delete)
     */
    public function delete(int $id)
    {
        $user = auth()->user();
        $event = $this->eventModel->find($id);

        if (!$event || $event->user_id !== $user->id) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        $this->eventModel->delete($id);
        return redirect()->to('organizer/events')->with('success', 'Evento excluído com sucesso!');
    }

    /**
     * Editor do mapa de assentos
     */
    public function seatMap(int $id)
    {
        $user = auth()->user();
        $event = $this->eventModel->findWithRelations($id);

        if (!$event || $event->user_id !== $user->id) {
            return redirect()->to('organizer/events')->with('error', 'Evento não encontrado.');
        }

        $sectorModel = model('SectorModel');
        $sectors = [];
        
        foreach ($event->sectors as $sector) {
            $sectors[] = $sectorModel->findWithSeats($sector->id);
        }

        $data = [
            'event'   => $event,
            'sectors' => $sectors,
            'colors'  => \App\Entities\Sector::getAvailableColors(),
        ];

        return view('organizer/events/seat_map', $data);
    }

    /**
     * Salva o mapa de assentos (AJAX)
     */
    public function saveSeatMap(int $id)
    {
        $user = auth()->user();
        $event = $this->eventModel->find($id);

        if (!$event || $event->user_id !== $user->id) {
            return $this->response->setJSON(['error' => 'Evento não encontrado.']);
        }

        try {
            $sectors = $this->request->getJSON(true)['sectors'] ?? [];
            
            $this->eventStoreService->updateSeatMap($id, $sectors);

            return $this->response->setJSON(['success' => true, 'message' => 'Mapa salvo com sucesso!']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Retorna layout do evento (AJAX)
     */
    public function getLayout(int $id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            return $this->response->setJSON(['error' => 'Evento não encontrado.']);
        }

        $seatModel = model('SeatModel');
        $seats = $seatModel->findForSeatMap($id);

        return $this->response->setJSON([
            'event' => $event,
            'seats' => $seats,
        ]);
    }

    /**
     * Extrai dados do evento do POST
     */
    protected function getEventDataFromPost(): array
    {
        return [
            'title'                    => $this->request->getPost('title'),
            'description'              => $this->request->getPost('description'),
            'venue_name'               => $this->request->getPost('venue_name'),
            'venue_address'            => $this->request->getPost('venue_address'),
            'venue_city'               => $this->request->getPost('venue_city'),
            'venue_state'              => $this->request->getPost('venue_state'),
            'venue_zipcode'            => $this->request->getPost('venue_zipcode'),
            'category'                 => $this->request->getPost('category'),
            'has_seat_map'             => (bool) $this->request->getPost('has_seat_map'),
            'max_tickets_per_purchase' => (int) $this->request->getPost('max_tickets_per_purchase') ?: 10,
        ];
    }

    /**
     * Upload de imagem
     */
    protected function uploadImage(string $field): ?string
    {
        $file = $this->request->getFile($field);

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/events', $newName);

        return $newName;
    }

    /**
     * Lista de estados brasileiros
     */
    protected function getBrazilianStates(): array
    {
        return [
            'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
            'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
            'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
            'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
            'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins',
        ];
    }
}
