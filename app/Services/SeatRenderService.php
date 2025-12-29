<?php

namespace App\Services;

class SeatRenderService
{
    protected $seatModel;
    protected $sectorModel;
    protected $rowModel;

    // Configurações de renderização
    protected $seatSize = 30;
    protected $seatGap = 5;
    protected $rowGap = 10;
    protected $sectorPadding = 20;

    public function __construct()
    {
        $this->seatModel = model('SeatModel');
        $this->sectorModel = model('SectorModel');
        $this->rowModel = model('RowModel');
    }

    /**
     * Renderiza o mapa de assentos completo de um evento
     */
    public function renderEventMap(int $eventId, int $eventDayId = null): array
    {
        $sectors = $this->sectorModel->findActiveByEvent($eventId);
        $mapData = [
            'sectors' => [],
            'legend'  => $this->getLegend(),
            'config'  => [
                'seatSize' => $this->seatSize,
                'seatGap'  => $this->seatGap,
                'rowGap'   => $this->rowGap,
            ],
        ];

        foreach ($sectors as $sector) {
            $sectorData = $this->renderSector($sector, $eventDayId);
            $mapData['sectors'][] = $sectorData;
        }

        return $mapData;
    }

    /**
     * Renderiza um setor com seus assentos
     */
    public function renderSector($sector, int $eventDayId = null): array
    {
        $rows = $this->rowModel->findBySectorWithSeats($sector->id);
        
        $sectorData = [
            'id'          => $sector->id,
            'name'        => $sector->name,
            'description' => $sector->description,
            'color'       => $sector->color,
            'price'       => $sector->price,
            'formattedPrice' => $sector->getFormattedPrice(),
            'position'    => [
                'x'      => $sector->position_x,
                'y'      => $sector->position_y,
                'width'  => $sector->width,
                'height' => $sector->height,
            ],
            'rows'        => [],
            'stats'       => [
                'total'     => 0,
                'available' => 0,
                'reserved'  => 0,
                'sold'      => 0,
                'blocked'   => 0,
            ],
        ];

        foreach ($rows as $row) {
            $rowData = $this->renderRow($row, $sector, $eventDayId);
            $sectorData['rows'][] = $rowData;
            
            // Atualiza estatísticas
            foreach ($rowData['seats'] as $seat) {
                $sectorData['stats']['total']++;
                $sectorData['stats'][$seat['status']]++;
            }
        }

        return $sectorData;
    }

    /**
     * Renderiza uma fila com seus assentos
     */
    public function renderRow($row, $sector, int $eventDayId = null): array
    {
        $rowData = [
            'id'         => $row->id,
            'name'       => $row->name,
            'row_number' => $row->row_number,
            'seats'      => [],
        ];

        // Se tiver dia específico, verifica reservas
        $bookingModel = null;
        if ($eventDayId) {
            $bookingModel = model('SeatBookingModel');
        }

        foreach ($row->seats as $seat) {
            $status = $seat->status;

            // Verifica se está reservado/vendido para este dia específico
            if ($eventDayId && $status === 'available') {
                if ($bookingModel->isSeatReserved($seat->id, $eventDayId)) {
                    $status = 'reserved';
                }
            }

            $rowData['seats'][] = [
                'id'           => $seat->id,
                'number'       => $seat->seat_number,
                'label'        => $seat->seat_label,
                'status'       => $status,
                'statusClass'  => $this->getStatusClass($status, $seat),
                'position'     => [
                    'x' => $seat->position_x,
                    'y' => $seat->position_y,
                ],
                'price'        => $seat->getPrice($sector->price),
                'is_wheelchair' => $seat->is_wheelchair,
                'is_companion'  => $seat->is_companion,
                'tooltip'      => $this->getSeatTooltip($seat, $sector),
                'selectable'   => $status === 'available',
            ];
        }

        return $rowData;
    }

    /**
     * Retorna a classe CSS do status do assento
     */
    protected function getStatusClass(string $status, $seat): string
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

        if ($seat->is_wheelchair) {
            $classes[] = 'seat-wheelchair';
        }

        if ($seat->is_companion) {
            $classes[] = 'seat-companion';
        }

        return implode(' ', $classes);
    }

    /**
     * Retorna o tooltip do assento
     */
    protected function getSeatTooltip($seat, $sector): string
    {
        $price = $seat->getPrice($sector->price);
        $formattedPrice = 'R$ ' . number_format($price, 2, ',', '.');
        
        $tooltip = "{$seat->seat_label} - {$formattedPrice}";

        if ($seat->is_wheelchair) {
            $tooltip .= ' (Acessível)';
        }

        return $tooltip;
    }

    /**
     * Retorna a legenda do mapa
     */
    public function getLegend(): array
    {
        return [
            [
                'class' => 'seat-available',
                'label' => 'Disponível',
                'color' => '#4CAF50',
            ],
            [
                'class' => 'seat-reserved',
                'label' => 'Reservado',
                'color' => '#FFC107',
            ],
            [
                'class' => 'seat-sold',
                'label' => 'Vendido',
                'color' => '#F44336',
            ],
            [
                'class' => 'seat-blocked',
                'label' => 'Bloqueado',
                'color' => '#9E9E9E',
            ],
            [
                'class' => 'seat-wheelchair',
                'label' => 'Acessível',
                'icon'  => 'bi-universal-access',
            ],
            [
                'class' => 'seat-selected',
                'label' => 'Selecionado',
                'color' => '#2196F3',
            ],
        ];
    }

    /**
     * Gera o HTML do mapa de assentos
     */
    public function generateMapHTML(int $eventId, int $eventDayId = null): string
    {
        $mapData = $this->renderEventMap($eventId, $eventDayId);
        
        $html = '<div class="seat-map-container">';
        
        // Palco
        $html .= '<div class="stage"><span>PALCO</span></div>';
        
        // Setores
        foreach ($mapData['sectors'] as $sector) {
            $html .= $this->generateSectorHTML($sector);
        }
        
        // Legenda
        $html .= $this->generateLegendHTML($mapData['legend']);
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Gera HTML de um setor
     */
    protected function generateSectorHTML(array $sector): string
    {
        $style = sprintf(
            'left: %dpx; top: %dpx; width: %dpx; min-height: %dpx;',
            $sector['position']['x'],
            $sector['position']['y'],
            $sector['position']['width'],
            $sector['position']['height']
        );

        $html = sprintf(
            '<div class="sector" data-sector-id="%d" style="%s">',
            $sector['id'],
            $style
        );

        $html .= sprintf(
            '<div class="sector-header" style="background-color: %s;">
                <span class="sector-name">%s</span>
                <span class="sector-price">%s</span>
            </div>',
            $sector['color'],
            esc($sector['name']),
            $sector['formattedPrice']
        );

        $html .= '<div class="sector-rows">';

        foreach ($sector['rows'] as $row) {
            $html .= $this->generateRowHTML($row);
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Gera HTML de uma fila
     */
    protected function generateRowHTML(array $row): string
    {
        $html = sprintf(
            '<div class="seat-row" data-row-id="%d">
                <span class="row-label">%s</span>
                <div class="seats">',
            $row['id'],
            esc($row['name'])
        );

        foreach ($row['seats'] as $seat) {
            $html .= $this->generateSeatHTML($seat);
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Gera HTML de um assento
     */
    protected function generateSeatHTML(array $seat): string
    {
        $dataAttrs = sprintf(
            'data-seat-id="%d" data-seat-label="%s" data-price="%s" data-status="%s"',
            $seat['id'],
            esc($seat['label']),
            $seat['price'],
            $seat['status']
        );

        $style = sprintf('left: %dpx; top: %dpx;', $seat['position']['x'], $seat['position']['y']);

        $icon = '';
        if ($seat['is_wheelchair']) {
            $icon = '<i class="bi bi-universal-access"></i>';
        }

        $disabled = $seat['selectable'] ? '' : 'disabled';

        return sprintf(
            '<button type="button" class="%s" %s style="%s" title="%s" %s>
                <span class="seat-number">%s</span>%s
            </button>',
            $seat['statusClass'],
            $dataAttrs,
            $style,
            esc($seat['tooltip']),
            $disabled,
            esc($seat['number']),
            $icon
        );
    }

    /**
     * Gera HTML da legenda
     */
    protected function generateLegendHTML(array $legend): string
    {
        $html = '<div class="seat-map-legend"><h6>Legenda</h6><div class="legend-items">';

        foreach ($legend as $item) {
            $style = isset($item['color']) ? "background-color: {$item['color']};" : '';
            $icon = isset($item['icon']) ? "<i class=\"bi {$item['icon']}\"></i>" : '';

            $html .= sprintf(
                '<div class="legend-item">
                    <span class="legend-seat" style="%s">%s</span>
                    <span class="legend-label">%s</span>
                </div>',
                $style,
                $icon,
                esc($item['label'])
            );
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Calcula dimensões necessárias para o mapa
     */
    public function calculateMapDimensions(int $eventId): array
    {
        $sectors = $this->sectorModel->findByEvent($eventId);
        
        $maxX = 0;
        $maxY = 0;

        foreach ($sectors as $sector) {
            $rightEdge = $sector->position_x + $sector->width;
            $bottomEdge = $sector->position_y + $sector->height;

            if ($rightEdge > $maxX) {
                $maxX = $rightEdge;
            }
            if ($bottomEdge > $maxY) {
                $maxY = $bottomEdge;
            }
        }

        return [
            'width'  => $maxX + 50, // Margem
            'height' => $maxY + 50,
        ];
    }

    /**
     * Exporta o mapa como JSON para o frontend
     */
    public function exportMapAsJSON(int $eventId, int $eventDayId = null): string
    {
        $mapData = $this->renderEventMap($eventId, $eventDayId);
        $dimensions = $this->calculateMapDimensions($eventId);
        
        $mapData['dimensions'] = $dimensions;
        
        return json_encode($mapData, JSON_UNESCAPED_UNICODE);
    }
}
