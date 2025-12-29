<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Seat;

class SeatModel extends Model
{
    protected $table            = 'seats';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Seat::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'row_id',
        'sector_id',
        'seat_number',
        'seat_label',
        'position_x',
        'position_y',
        'status',
        'is_wheelchair',
        'is_companion',
        'price_override',
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
        'row_id'      => 'required|integer',
        'sector_id'   => 'required|integer',
        'seat_number' => 'required|max_length[10]',
        'seat_label'  => 'required|max_length[20]',
    ];

    protected $skipValidation = false;

    /**
     * Busca assentos de uma fila
     */
    public function findByRow(int $rowId)
    {
        return $this->where('row_id', $rowId)
                    ->orderBy('seat_number', 'ASC')
                    ->findAll();
    }

    /**
     * Busca assentos de um setor
     */
    public function findBySector(int $sectorId)
    {
        return $this->where('sector_id', $sectorId)
                    ->orderBy('row_id', 'ASC')
                    ->orderBy('seat_number', 'ASC')
                    ->findAll();
    }

    /**
     * Busca assentos disponíveis de um setor
     */
    public function findAvailableBySector(int $sectorId)
    {
        return $this->where('sector_id', $sectorId)
                    ->where('status', 'available')
                    ->orderBy('row_id', 'ASC')
                    ->orderBy('seat_number', 'ASC')
                    ->findAll();
    }

    /**
     * Conta assentos por status em um setor
     */
    public function countByStatusInSector(int $sectorId): array
    {
        $results = $this->select('status, COUNT(*) as total')
                        ->where('sector_id', $sectorId)
                        ->groupBy('status')
                        ->findAll();
        
        $counts = [
            'available' => 0,
            'reserved'  => 0,
            'sold'      => 0,
            'blocked'   => 0,
        ];
        
        foreach ($results as $row) {
            $counts[$row->status] = (int) $row->total;
        }
        
        return $counts;
    }

    /**
     * Atualiza status de vários assentos
     */
    public function updateStatus(array $seatIds, string $status): bool
    {
        return $this->whereIn('id', $seatIds)
                    ->set(['status' => $status])
                    ->update();
    }

    /**
     * Reserva assentos temporariamente
     */
    public function reserveSeats(array $seatIds): bool
    {
        return $this->whereIn('id', $seatIds)
                    ->where('status', 'available')
                    ->set(['status' => 'reserved'])
                    ->update();
    }

    /**
     * Libera assentos reservados
     */
    public function releaseSeats(array $seatIds): bool
    {
        return $this->whereIn('id', $seatIds)
                    ->where('status', 'reserved')
                    ->set(['status' => 'available'])
                    ->update();
    }

    /**
     * Confirma venda de assentos
     */
    public function confirmSeats(array $seatIds): bool
    {
        return $this->whereIn('id', $seatIds)
                    ->set(['status' => 'sold'])
                    ->update();
    }

    /**
     * Deleta assentos de um setor
     */
    public function deleteBySector(int $sectorId): bool
    {
        return $this->where('sector_id', $sectorId)->delete();
    }

    /**
     * Deleta assentos de uma fila
     */
    public function deleteByRow(int $rowId): bool
    {
        return $this->where('row_id', $rowId)->delete();
    }

    /**
     * Busca assentos para o mapa
     */
    public function findForSeatMap(int $eventId): array
    {
        return $this->select('seats.*, sectors.name as sector_name, sectors.color as sector_color, sectors.price as sector_price, rows.name as row_name')
                    ->join('sectors', 'sectors.id = seats.sector_id')
                    ->join('rows', 'rows.id = seats.row_id')
                    ->where('sectors.event_id', $eventId)
                    ->orderBy('sectors.sort_order', 'ASC')
                    ->orderBy('rows.row_number', 'ASC')
                    ->orderBy('seats.seat_number', 'ASC')
                    ->findAll();
    }
}
