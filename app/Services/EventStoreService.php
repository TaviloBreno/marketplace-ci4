<?php

namespace App\Services;

use App\Entities\Event;
use App\Entities\EventDay;
use App\Entities\Sector;
use App\Entities\Row;
use App\Entities\Seat;

class EventStoreService
{
    protected $eventModel;
    protected $eventDayModel;
    protected $sectorModel;
    protected $rowModel;
    protected $seatModel;

    public function __construct()
    {
        $this->eventModel = model('EventModel');
        $this->eventDayModel = model('EventDayModel');
        $this->sectorModel = model('SectorModel');
        $this->rowModel = model('RowModel');
        $this->seatModel = model('SeatModel');
    }

    /**
     * Cria um novo evento
     */
    public function createEvent(array $data): Event
    {
        $event = new Event($data);
        
        $this->eventModel->save($event);
        $event->id = $this->eventModel->getInsertID();
        
        return $event;
    }

    /**
     * Atualiza um evento
     */
    public function updateEvent(int $eventId, array $data): bool
    {
        return $this->eventModel->update($eventId, $data);
    }

    /**
     * Cria os dias do evento
     */
    public function createEventDays(int $eventId, array $days): array
    {
        $createdDays = [];

        foreach ($days as $dayData) {
            if (empty($dayData['date']) || empty($dayData['start_time'])) {
                continue;
            }

            $eventDay = new EventDay([
                'event_id'        => $eventId,
                'date'            => $dayData['date'],
                'start_time'      => $dayData['start_time'],
                'end_time'        => $dayData['end_time'] ?? null,
                'doors_open_time' => $dayData['doors_open_time'] ?? null,
                'is_active'       => 1,
            ]);

            $this->eventDayModel->save($eventDay);
            $eventDay->id = $this->eventDayModel->getInsertID();
            $createdDays[] = $eventDay;
        }

        return $createdDays;
    }

    /**
     * Atualiza os dias do evento
     */
    public function updateEventDays(int $eventId, array $days): void
    {
        // Remove dias existentes
        $this->eventDayModel->deleteByEvent($eventId);

        // Cria novos dias
        if (!empty($days)) {
            $this->createEventDays($eventId, $days);
        }
    }

    /**
     * Cria setores do evento
     */
    public function createSectors(int $eventId, array $sectors): array
    {
        $createdSectors = [];

        foreach ($sectors as $index => $sectorData) {
            if (empty($sectorData['name'])) {
                continue;
            }

            $sector = new Sector([
                'event_id'    => $eventId,
                'name'        => $sectorData['name'],
                'description' => $sectorData['description'] ?? null,
                'color'       => $sectorData['color'] ?? '#3498db',
                'price'       => (float) ($sectorData['price'] ?? 0),
                'capacity'    => (int) ($sectorData['capacity'] ?? 0),
                'position_x'  => (int) ($sectorData['position_x'] ?? 0),
                'position_y'  => (int) ($sectorData['position_y'] ?? 0),
                'width'       => (int) ($sectorData['width'] ?? 200),
                'height'      => (int) ($sectorData['height'] ?? 100),
                'sort_order'  => $index,
                'is_active'   => 1,
            ]);

            $this->sectorModel->save($sector);
            $sector->id = $this->sectorModel->getInsertID();

            // Cria filas e assentos se especificado
            if (!empty($sectorData['rows'])) {
                $this->createRows($sector->id, $sectorData['rows']);
            } elseif (!empty($sectorData['rows_count']) && !empty($sectorData['seats_per_row'])) {
                $this->generateSeats($sector->id, (int) $sectorData['rows_count'], (int) $sectorData['seats_per_row']);
            }

            $createdSectors[] = $sector;
        }

        return $createdSectors;
    }

    /**
     * Cria filas em um setor
     */
    public function createRows(int $sectorId, array $rows): array
    {
        $createdRows = [];

        foreach ($rows as $index => $rowData) {
            $row = new Row([
                'sector_id'    => $sectorId,
                'name'         => $rowData['name'] ?? Row::generateLabel($index),
                'seats_count'  => (int) ($rowData['seats_count'] ?? 0),
                'row_number'   => $index,
                'curve_offset' => (int) ($rowData['curve_offset'] ?? 0),
            ]);

            $this->rowModel->save($row);
            $row->id = $this->rowModel->getInsertID();

            // Cria assentos
            if (!empty($rowData['seats'])) {
                $this->createSeats($row->id, $sectorId, $rowData['seats'], $row->name);
            } elseif ($row->seats_count > 0) {
                $this->generateRowSeats($row->id, $sectorId, $row->seats_count, $row->name);
            }

            $createdRows[] = $row;
        }

        return $createdRows;
    }

    /**
     * Cria assentos em uma fila
     */
    public function createSeats(int $rowId, int $sectorId, array $seats, string $rowLabel): array
    {
        $createdSeats = [];

        foreach ($seats as $index => $seatData) {
            $seatNumber = $seatData['seat_number'] ?? ($index + 1);
            
            $seat = new Seat([
                'row_id'         => $rowId,
                'sector_id'      => $sectorId,
                'seat_number'    => $seatNumber,
                'seat_label'     => $rowLabel . $seatNumber,
                'position_x'     => (int) ($seatData['position_x'] ?? ($index * 35)),
                'position_y'     => (int) ($seatData['position_y'] ?? 0),
                'status'         => $seatData['status'] ?? 'available',
                'is_wheelchair'  => (bool) ($seatData['is_wheelchair'] ?? false),
                'is_companion'   => (bool) ($seatData['is_companion'] ?? false),
                'price_override' => $seatData['price_override'] ?? null,
            ]);

            $this->seatModel->save($seat);
            $seat->id = $this->seatModel->getInsertID();
            $createdSeats[] = $seat;
        }

        return $createdSeats;
    }

    /**
     * Gera assentos automaticamente para um setor
     */
    public function generateSeats(int $sectorId, int $rowsCount, int $seatsPerRow): void
    {
        for ($r = 0; $r < $rowsCount; $r++) {
            $rowLabel = Row::generateLabel($r);
            
            $row = new Row([
                'sector_id'   => $sectorId,
                'name'        => $rowLabel,
                'seats_count' => $seatsPerRow,
                'row_number'  => $r,
            ]);

            $this->rowModel->save($row);
            $rowId = $this->rowModel->getInsertID();

            $this->generateRowSeats($rowId, $sectorId, $seatsPerRow, $rowLabel, $r);
        }

        // Atualiza capacidade do setor
        $this->sectorModel->update($sectorId, [
            'capacity' => $rowsCount * $seatsPerRow,
        ]);
    }

    /**
     * Gera assentos para uma fila
     */
    protected function generateRowSeats(int $rowId, int $sectorId, int $seatsCount, string $rowLabel, int $rowIndex = 0): void
    {
        $seats = [];

        for ($s = 1; $s <= $seatsCount; $s++) {
            $seats[] = [
                'row_id'      => $rowId,
                'sector_id'   => $sectorId,
                'seat_number' => (string) $s,
                'seat_label'  => $rowLabel . $s,
                'position_x'  => ($s - 1) * 35,
                'position_y'  => $rowIndex * 35,
                'status'      => 'available',
            ];
        }

        $this->seatModel->insertBatch($seats);
    }

    /**
     * Atualiza o mapa de assentos
     */
    public function updateSeatMap(int $eventId, array $sectors): void
    {
        // Para cada setor enviado
        foreach ($sectors as $sectorData) {
            $sectorId = $sectorData['id'] ?? null;

            if ($sectorId) {
                // Atualiza setor existente
                $this->sectorModel->update($sectorId, [
                    'name'        => $sectorData['name'],
                    'color'       => $sectorData['color'],
                    'price'       => $sectorData['price'],
                    'position_x'  => $sectorData['position_x'],
                    'position_y'  => $sectorData['position_y'],
                    'width'       => $sectorData['width'],
                    'height'      => $sectorData['height'],
                ]);

                // Atualiza assentos
                if (!empty($sectorData['seats'])) {
                    foreach ($sectorData['seats'] as $seatData) {
                        if (!empty($seatData['id'])) {
                            $this->seatModel->update($seatData['id'], [
                                'position_x'    => $seatData['position_x'],
                                'position_y'    => $seatData['position_y'],
                                'status'        => $seatData['status'] ?? 'available',
                                'is_wheelchair' => $seatData['is_wheelchair'] ?? false,
                            ]);
                        }
                    }
                }
            } else {
                // Cria novo setor
                $this->createSectors($eventId, [$sectorData]);
            }
        }
    }

    /**
     * Remove um setor e seus assentos
     */
    public function deleteSector(int $sectorId): void
    {
        // Remove assentos
        $this->seatModel->deleteBySector($sectorId);
        
        // Remove filas
        $this->rowModel->deleteBySector($sectorId);
        
        // Remove setor
        $this->sectorModel->delete($sectorId);
    }

    /**
     * Duplica um evento
     */
    public function duplicateEvent(int $eventId): Event
    {
        $originalEvent = $this->eventModel->find($eventId);
        
        if (!$originalEvent) {
            throw new \Exception('Evento não encontrado.');
        }

        // Cria cópia do evento
        $newEventData = [
            'user_id'                  => $originalEvent->user_id,
            'title'                    => $originalEvent->title . ' (Cópia)',
            'description'              => $originalEvent->description,
            'venue_name'               => $originalEvent->venue_name,
            'venue_address'            => $originalEvent->venue_address,
            'venue_city'               => $originalEvent->venue_city,
            'venue_state'              => $originalEvent->venue_state,
            'venue_zipcode'            => $originalEvent->venue_zipcode,
            'category'                 => $originalEvent->category,
            'status'                   => 'draft',
            'has_seat_map'             => $originalEvent->has_seat_map,
            'max_tickets_per_purchase' => $originalEvent->max_tickets_per_purchase,
        ];

        $newEvent = $this->createEvent($newEventData);

        // Copia setores (sem assentos vendidos)
        $sectors = $this->sectorModel->findByEvent($eventId);
        
        foreach ($sectors as $sector) {
            $sectorWithSeats = $this->sectorModel->findWithSeats($sector->id);
            
            $newSectorData = [
                'name'        => $sector->name,
                'description' => $sector->description,
                'color'       => $sector->color,
                'price'       => $sector->price,
                'capacity'    => $sector->capacity,
                'position_x'  => $sector->position_x,
                'position_y'  => $sector->position_y,
                'width'       => $sector->width,
                'height'      => $sector->height,
                'rows'        => [],
            ];

            foreach ($sectorWithSeats->rows as $row) {
                $rowData = [
                    'name'         => $row->name,
                    'seats_count'  => $row->seats_count,
                    'row_number'   => $row->row_number,
                    'curve_offset' => $row->curve_offset,
                    'seats'        => [],
                ];

                foreach ($row->seats as $seat) {
                    $rowData['seats'][] = [
                        'seat_number'   => $seat->seat_number,
                        'position_x'    => $seat->position_x,
                        'position_y'    => $seat->position_y,
                        'status'        => 'available', // Reseta status
                        'is_wheelchair' => $seat->is_wheelchair,
                        'is_companion'  => $seat->is_companion,
                    ];
                }

                $newSectorData['rows'][] = $rowData;
            }

            $this->createSectors($newEvent->id, [$newSectorData]);
        }

        return $newEvent;
    }
}
