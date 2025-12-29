<?php

namespace App\Services;

use App\Models\SectorModel;
use App\Models\QueueModel;
use App\Models\SeatModel;
use App\Models\SeatBookingModel;

class SeatRenderService
{
    protected SectorModel $sectorModel;
    protected QueueModel $queueModel;
    protected SeatModel $seatModel;
    protected SeatBookingModel $seatBookingModel;

    protected int $seatWidth = 30;
    protected int $seatHeight = 30;
    protected int $seatGap = 5;

    public function __construct()
    {
        $this->sectorModel = model('SectorModel');
        $this->queueModel = model('QueueModel');
        $this->seatModel = model('SeatModel');
        $this->seatBookingModel = model('SeatBookingModel');
    }

    /**
     * Renderiza o layout completo do evento
     */
    public function renderEventLayout(int $eventId, int $eventDayId = null): array
    {
        $sectors = $this->sectorModel->findActiveByEvent($eventId);
        $layout = [
            'sectors' => [],
            'stats'   => [
                'total'     => 0,
                'available' => 0,
                'reserved'  => 0,
                'sold'      => 0,
                'blocked'   => 0,
            ],
        ];

        foreach ($sectors as $sector) {
            $sectorData = $this->renderSector($sector, $eventDayId);
            $layout['sectors'][] = $sectorData;

            // Acumular estatísticas
            $layout['stats']['total'] += $sectorData['stats']['total'];
            $layout['stats']['available'] += $sectorData['stats']['available'];
            $layout['stats']['reserved'] += $sectorData['stats']['reserved'];
            $layout['stats']['sold'] += $sectorData['stats']['sold'];
            $layout['stats']['blocked'] += $sectorData['stats']['blocked'];
        }

        return $layout;
    }

    /**
     * Renderiza um setor com suas filas e assentos
     */
    public function renderSector($sector, int $eventDayId = null): array
    {
        $queues = $this->queueModel->findActiveBySector($sector->id);
        
        $sectorData = [
            'id'          => $sector->id,
            'name'        => $sector->name,
            'description' => $sector->description,
            'color'       => $sector->color,
            'price'       => $sector->price,
            'is_numbered' => $sector->is_numbered,
            'position'    => [
                'x'      => $sector->position_x,
                'y'      => $sector->position_y,
                'width'  => $sector->width,
                'height' => $sector->height,
            ],
            'queues' => [],
            'stats'  => [
                'total'     => 0,
                'available' => 0,
                'reserved'  => 0,
                'sold'      => 0,
                'blocked'   => 0,
            ],
        ];

        foreach ($queues as $queue) {
            $queueData = $this->renderQueue($queue, $eventDayId, $sector->price);
            $sectorData['queues'][] = $queueData;

            // Acumular estatísticas
            $sectorData['stats']['total'] += $queueData['stats']['total'];
            $sectorData['stats']['available'] += $queueData['stats']['available'];
            $sectorData['stats']['reserved'] += $queueData['stats']['reserved'];
            $sectorData['stats']['sold'] += $queueData['stats']['sold'];
            $sectorData['stats']['blocked'] += $queueData['stats']['blocked'];
        }

        return $sectorData;
    }

    /**
     * Renderiza uma fila com seus assentos
     */
    public function renderQueue($queue, int $eventDayId = null, float $sectorPrice = 0): array
    {
        $seats = $this->seatModel->findByQueue($queue->id);
        
        $queueData = [
            'id'          => $queue->id,
            'name'        => $queue->name,
            'total_seats' => $queue->total_seats,
            'position'    => [
                'x' => $queue->position_x,
                'y' => $queue->position_y,
            ],
            'curve_angle' => $queue->curve_angle,
            'seats'       => [],
            'stats'       => [
                'total'     => 0,
                'available' => 0,
                'reserved'  => 0,
                'sold'      => 0,
                'blocked'   => 0,
            ],
        ];

        foreach ($seats as $index => $seat) {
            $seatStatus = $this->getSeatStatus($seat, $eventDayId);
            
            $seatData = [
                'id'            => $seat->id,
                'number'        => $seat->number,
                'label'         => $seat->label ?? $seat->number,
                'position'      => $this->calculateSeatPosition($index, $queue->curve_angle),
                'status'        => $seatStatus,
                'is_accessible' => (bool) $seat->is_accessible,
                'price'         => $sectorPrice,
                'css_class'     => $this->getSeatCssClass($seatStatus, $seat->is_accessible),
            ];

            $queueData['seats'][] = $seatData;
            $queueData['stats']['total']++;
            $queueData['stats'][$seatStatus]++;
        }

        return $queueData;
    }

    /**
     * Obtém o status de um assento
     */
    protected function getSeatStatus($seat, int $eventDayId = null): string
    {
        // Verifica status base do assento
        if ($seat->status === 'blocked') {
            return 'blocked';
        }

        if ($seat->status === 'maintenance') {
            return 'blocked';
        }

        // Se não tem dia específico, retorna disponível
        if (!$eventDayId) {
            return 'available';
        }

        // Verifica reservas
        $booking = $this->seatBookingModel
            ->where('seat_id', $seat->id)
            ->where('event_day_id', $eventDayId)
            ->whereIn('status', ['reserved', 'confirmed'])
            ->first();

        if (!$booking) {
            return 'available';
        }

        // Verifica se a reserva expirou
        if ($booking->status === 'reserved' && $booking->isExpired()) {
            return 'available';
        }

        return $booking->status === 'confirmed' ? 'sold' : 'reserved';
    }

    /**
     * Calcula a posição de um assento considerando curvas
     */
    protected function calculateSeatPosition(int $index, int $curveAngle = 0): array
    {
        $baseX = $index * ($this->seatWidth + $this->seatGap);
        $baseY = 0;

        if ($curveAngle !== 0) {
            // Calcular posição em arco
            $angleRad = deg2rad($curveAngle);
            $radius = 500; // Raio do arco
            $step = $angleRad / max(1, $index + 1);
            
            $baseX = $radius * sin($step * $index);
            $baseY = $radius * (1 - cos($step * $index));
        }

        return [
            'x' => (int) $baseX,
            'y' => (int) $baseY,
        ];
    }

    /**
     * Retorna a classe CSS do assento
     */
    protected function getSeatCssClass(string $status, bool $isAccessible = false): string
    {
        $classes = ['seat'];

        switch ($status) {
            case 'available':
                $classes[] = 'seat-available';
                break;
            case 'reserved':
                $classes[] = 'seat-reserved';
                break;
            case 'sold':
                $classes[] = 'seat-sold';
                break;
            case 'blocked':
                $classes[] = 'seat-blocked';
                break;
        }

        if ($isAccessible) {
            $classes[] = 'seat-accessible';
        }

        return implode(' ', $classes);
    }

    /**
     * Gera o HTML do mapa de assentos
     */
    public function generateSeatMapHtml(int $eventId, int $eventDayId = null): string
    {
        $layout = $this->renderEventLayout($eventId, $eventDayId);
        
        $html = '<div class="seat-map-container">';
        $html .= '<div class="seat-map">';

        // Palco
        $html .= '<div class="stage"><span>PALCO</span></div>';

        foreach ($layout['sectors'] as $sector) {
            $html .= $this->generateSectorHtml($sector);
        }

        $html .= '</div>';
        
        // Legenda
        $html .= $this->generateLegendHtml();
        
        // Estatísticas
        $html .= $this->generateStatsHtml($layout['stats']);
        
        $html .= '</div>';

        return $html;
    }

    /**
     * Gera HTML de um setor
     */
    protected function generateSectorHtml(array $sector): string
    {
        $style = sprintf(
            'left: %dpx; top: %dpx; width: %dpx; min-height: %dpx; background-color: %s20; border-color: %s;',
            $sector['position']['x'],
            $sector['position']['y'],
            $sector['position']['width'],
            $sector['position']['height'],
            $sector['color'],
            $sector['color']
        );

        $html = '<div class="sector" style="' . $style . '" data-sector-id="' . $sector['id'] . '">';
        $html .= '<div class="sector-header">';
        $html .= '<span class="sector-name">' . esc($sector['name']) . '</span>';
        $html .= '<span class="sector-price">R$ ' . number_format($sector['price'], 2, ',', '.') . '</span>';
        $html .= '</div>';

        if ($sector['is_numbered']) {
            foreach ($sector['queues'] as $queue) {
                $html .= $this->generateQueueHtml($queue, $sector['color']);
            }
        } else {
            // Setor sem numeração (pista)
            $html .= '<div class="sector-general-area">';
            $html .= '<span class="available-count">' . $sector['stats']['available'] . ' disponíveis</span>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Gera HTML de uma fila
     */
    protected function generateQueueHtml(array $queue, string $sectorColor): string
    {
        $html = '<div class="queue" data-queue-id="' . $queue['id'] . '">';
        $html .= '<span class="queue-name">Fila ' . esc($queue['name']) . '</span>';
        $html .= '<div class="seats-row">';

        foreach ($queue['seats'] as $seat) {
            $html .= $this->generateSeatHtml($seat, $sectorColor);
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Gera HTML de um assento
     */
    protected function generateSeatHtml(array $seat, string $sectorColor): string
    {
        $dataAttrs = sprintf(
            'data-seat-id="%d" data-seat-label="%s" data-price="%.2f" data-status="%s"',
            $seat['id'],
            esc($seat['label']),
            $seat['price'],
            $seat['status']
        );

        $style = '';
        if ($seat['status'] === 'available') {
            $style = 'background-color: ' . $sectorColor . ';';
        }

        $title = $seat['label'] . ' - R$ ' . number_format($seat['price'], 2, ',', '.');
        if ($seat['status'] !== 'available') {
            $title .= ' (' . ucfirst($seat['status']) . ')';
        }

        $html = '<div class="' . $seat['css_class'] . '" ' . $dataAttrs . ' style="' . $style . '" title="' . esc($title) . '">';
        $html .= '<span class="seat-number">' . esc($seat['number']) . '</span>';
        
        if ($seat['is_accessible']) {
            $html .= '<i class="bi bi-wheelchair seat-icon"></i>';
        }
        
        $html .= '</div>';

        return $html;
    }

    /**
     * Gera HTML da legenda
     */
    protected function generateLegendHtml(): string
    {
        $html = '<div class="seat-legend">';
        $html .= '<div class="legend-item"><div class="seat seat-available"></div><span>Disponível</span></div>';
        $html .= '<div class="legend-item"><div class="seat seat-reserved"></div><span>Reservado</span></div>';
        $html .= '<div class="legend-item"><div class="seat seat-sold"></div><span>Vendido</span></div>';
        $html .= '<div class="legend-item"><div class="seat seat-blocked"></div><span>Bloqueado</span></div>';
        $html .= '<div class="legend-item"><div class="seat seat-accessible"><i class="bi bi-wheelchair"></i></div><span>PCD</span></div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Gera HTML das estatísticas
     */
    protected function generateStatsHtml(array $stats): string
    {
        $html = '<div class="seat-stats">';
        $html .= '<div class="stat-item"><strong>' . $stats['available'] . '</strong> Disponíveis</div>';
        $html .= '<div class="stat-item"><strong>' . $stats['reserved'] . '</strong> Reservados</div>';
        $html .= '<div class="stat-item"><strong>' . $stats['sold'] . '</strong> Vendidos</div>';
        $html .= '<div class="stat-item"><strong>' . $stats['total'] . '</strong> Total</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Retorna os dados do layout em JSON para uso com JavaScript
     */
    public function getLayoutJson(int $eventId, int $eventDayId = null): string
    {
        $layout = $this->renderEventLayout($eventId, $eventDayId);
        return json_encode($layout);
    }
}
