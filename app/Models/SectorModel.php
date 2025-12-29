<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Sector;

class SectorModel extends Model
{
    protected $table            = 'sectors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Sector::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'event_id',
        'name',
        'description',
        'color',
        'price',
        'capacity',
        'position_x',
        'position_y',
        'width',
        'height',
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
        'event_id' => 'required|integer',
        'name'     => 'required|min_length[1]|max_length[100]',
        'price'    => 'required|decimal',
    ];

    protected $skipValidation = false;

    /**
     * Busca setores de um evento
     */
    public function findByEvent(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Busca setores ativos de um evento
     */
    public function findActiveByEvent(int $eventId)
    {
        return $this->where('event_id', $eventId)
                    ->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Busca setor com suas filas e assentos
     */
    public function findWithSeats(int $sectorId)
    {
        $sector = $this->find($sectorId);
        
        if (!$sector) {
            return null;
        }
        
        $rowModel = model('RowModel');
        $sector->rows = $rowModel->findBySectorWithSeats($sectorId);
        
        return $sector;
    }

    /**
     * Deleta setores de um evento
     */
    public function deleteByEvent(int $eventId): bool
    {
        return $this->where('event_id', $eventId)->delete();
    }

    /**
     * Retorna estatísticas dos setores de um evento
     */
    public function getStats(int $eventId): array
    {
        $sectors = $this->findByEvent($eventId);
        $seatModel = model('SeatModel');
        
        $stats = [];
        
        foreach ($sectors as $sector) {
            $totalSeats = $seatModel->where('sector_id', $sector->id)->countAllResults();
            $availableSeats = $seatModel->where('sector_id', $sector->id)
                                        ->where('status', 'available')
                                        ->countAllResults();
            $soldSeats = $seatModel->where('sector_id', $sector->id)
                                   ->where('status', 'sold')
                                   ->countAllResults();
            
            $stats[] = [
                'sector'    => $sector,
                'total'     => $totalSeats,
                'available' => $availableSeats,
                'sold'      => $soldSeats,
                'revenue'   => $soldSeats * $sector->price,
            ];
        }
        
        return $stats;
    }
}
