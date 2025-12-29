<?php

namespace App\Services;

use App\Models\EventModel;
use App\Models\EventDayModel;
use App\Models\SectorModel;
use App\Models\QueueModel;
use App\Models\SeatModel;
use App\Entities\Event;
use CodeIgniter\Database\Exceptions\DatabaseException;

class EventStoreService
{
    protected EventModel $eventModel;
    protected EventDayModel $eventDayModel;
    protected SectorModel $sectorModel;
    protected QueueModel $queueModel;
    protected SeatModel $seatModel;
    protected $db;

    public function __construct()
    {
        $this->eventModel = model('EventModel');
        $this->eventDayModel = model('EventDayModel');
        $this->sectorModel = model('SectorModel');
        $this->queueModel = model('QueueModel');
        $this->seatModel = model('SeatModel');
        $this->db = \Config\Database::connect();
    }

    /**
     * Cria um evento completo com dias, setores, filas e assentos
     */
    public function createEvent(array $eventData, array $days, array $sectors): array
    {
        $this->db->transStart();

        try {
            // Criar evento
            $event = new Event($eventData);
            $event->slug = $this->eventModel->generateUniqueSlug($eventData['title']);
            
            $eventId = $this->eventModel->insert($event);

            if (!$eventId) {
                throw new \Exception('Erro ao criar evento: ' . implode(', ', $this->eventModel->errors()));
            }

            // Criar dias do evento
            foreach ($days as $day) {
                $day['event_id'] = $eventId;
                $this->eventDayModel->insert($day);
            }

            // Criar setores, filas e assentos
            foreach ($sectors as $sectorIndex => $sectorData) {
                $queuesData = $sectorData['queues'] ?? [];
                unset($sectorData['queues']);

                $sectorData['event_id'] = $eventId;
                $sectorData['sort_order'] = $sectorIndex;

                $sectorId = $this->sectorModel->insert($sectorData);

                if (!$sectorId) {
                    throw new \Exception('Erro ao criar setor: ' . implode(', ', $this->sectorModel->errors()));
                }

                // Criar filas e assentos
                foreach ($queuesData as $queueIndex => $queueData) {
                    $seatsCount = $queueData['total_seats'] ?? 0;
                    unset($queueData['total_seats']);

                    $queueData['sector_id'] = $sectorId;
                    $queueData['sort_order'] = $queueIndex;
                    $queueData['total_seats'] = $seatsCount;

                    $queueId = $this->queueModel->insert($queueData);

                    if (!$queueId) {
                        throw new \Exception('Erro ao criar fila: ' . implode(', ', $this->queueModel->errors()));
                    }

                    // Criar assentos
                    $this->createSeatsForQueue($queueId, $queueData['name'], $seatsCount);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Erro na transação do banco de dados.');
            }

            return [
                'success'  => true,
                'event_id' => $eventId,
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'EventStoreService::createEvent - ' . $e->getMessage());

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Cria assentos para uma fila
     */
    protected function createSeatsForQueue(int $queueId, string $queueName, int $totalSeats): void
    {
        $seats = [];
        
        for ($i = 1; $i <= $totalSeats; $i++) {
            $seats[] = [
                'queue_id'   => $queueId,
                'number'     => (string) $i,
                'label'      => $queueName . '-' . $i,
                'position_x' => ($i - 1) * 35,
                'position_y' => 0,
                'status'     => 'available',
                'sort_order' => $i,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        if (!empty($seats)) {
            $this->seatModel->insertBatch($seats);
        }
    }

    /**
     * Atualiza um evento
     */
    public function updateEvent(int $eventId, array $eventData): array
    {
        try {
            $event = $this->eventModel->find($eventId);

            if (!$event) {
                return [
                    'success' => false,
                    'error'   => 'Evento não encontrado.',
                ];
            }

            // Gerar novo slug se o título mudou
            if (isset($eventData['title']) && $eventData['title'] !== $event->title) {
                $eventData['slug'] = $this->eventModel->generateUniqueSlug($eventData['title'], $eventId);
            }

            $this->eventModel->update($eventId, $eventData);

            return [
                'success' => true,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Adiciona um dia ao evento
     */
    public function addEventDay(int $eventId, array $dayData): array
    {
        try {
            $dayData['event_id'] = $eventId;
            $dayId = $this->eventDayModel->insert($dayData);

            return [
                'success' => true,
                'day_id'  => $dayId,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Adiciona um setor ao evento
     */
    public function addSector(int $eventId, array $sectorData): array
    {
        try {
            $sectorData['event_id'] = $eventId;
            $sectorId = $this->sectorModel->insert($sectorData);

            return [
                'success'   => true,
                'sector_id' => $sectorId,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Adiciona uma fila a um setor
     */
    public function addQueue(int $sectorId, array $queueData): array
    {
        try {
            $totalSeats = $queueData['total_seats'] ?? 0;
            $queueData['sector_id'] = $sectorId;
            
            $queueId = $this->queueModel->insert($queueData);

            // Criar assentos
            if ($totalSeats > 0) {
                $this->createSeatsForQueue($queueId, $queueData['name'], $totalSeats);
            }

            return [
                'success'  => true,
                'queue_id' => $queueId,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Publica um evento
     */
    public function publishEvent(int $eventId): array
    {
        $event = $this->eventModel->find($eventId);

        if (!$event) {
            return [
                'success' => false,
                'error'   => 'Evento não encontrado.',
            ];
        }

        // Verificar se tem pelo menos um dia
        $days = $this->eventDayModel->findByEvent($eventId);
        if (empty($days)) {
            return [
                'success' => false,
                'error'   => 'O evento precisa ter pelo menos uma data.',
            ];
        }

        // Verificar se tem pelo menos um setor
        $sectors = $this->sectorModel->findByEvent($eventId);
        if (empty($sectors)) {
            return [
                'success' => false,
                'error'   => 'O evento precisa ter pelo menos um setor.',
            ];
        }

        $this->eventModel->update($eventId, ['status' => 'published']);

        return [
            'success' => true,
        ];
    }

    /**
     * Cancela um evento
     */
    public function cancelEvent(int $eventId): array
    {
        $event = $this->eventModel->find($eventId);

        if (!$event) {
            return [
                'success' => false,
                'error'   => 'Evento não encontrado.',
            ];
        }

        // TODO: Verificar se há pedidos e processar reembolsos

        $this->eventModel->update($eventId, ['status' => 'cancelled']);

        return [
            'success' => true,
        ];
    }

    /**
     * Obtém dados completos do evento para edição
     */
    public function getEventForEdit(int $eventId): ?array
    {
        $event = $this->eventModel->find($eventId);

        if (!$event) {
            return null;
        }

        $days = $this->eventDayModel->findByEvent($eventId);
        $sectors = $this->sectorModel->findWithSeatCounts($eventId);

        // Carregar filas e assentos para cada setor
        foreach ($sectors as &$sector) {
            $sector->queues = $this->queueModel->findBySector($sector->id);
        }

        return [
            'event'   => $event,
            'days'    => $days,
            'sectors' => $sectors,
        ];
    }

    /**
     * Faz upload da imagem do evento
     */
    public function uploadImage(int $eventId, $file, string $type = 'image'): array
    {
        if (!$file->isValid()) {
            return [
                'success' => false,
                'error'   => $file->getErrorString(),
            ];
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/events', $newName);

        $this->eventModel->update($eventId, [$type => $newName]);

        return [
            'success'  => true,
            'filename' => $newName,
        ];
    }
}
