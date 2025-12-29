<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Row;

class RowModel extends Model
{
    protected $table            = 'rows';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Row::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sector_id',
        'name',
        'seats_count',
        'row_number',
        'curve_offset',
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
        'sector_id'   => 'required|integer',
        'name'        => 'required|max_length[10]',
        'row_number'  => 'required|integer',
        'seats_count' => 'required|integer',
    ];

    protected $skipValidation = false;

    /**
     * Busca filas de um setor
     */
    public function findBySector(int $sectorId)
    {
        return $this->where('sector_id', $sectorId)
                    ->orderBy('row_number', 'ASC')
                    ->findAll();
    }

    /**
     * Busca filas de um setor com assentos
     */
    public function findBySectorWithSeats(int $sectorId)
    {
        $rows = $this->findBySector($sectorId);
        $seatModel = model('SeatModel');
        
        foreach ($rows as &$row) {
            $row->seats = $seatModel->findByRow($row->id);
        }
        
        return $rows;
    }

    /**
     * Deleta filas de um setor
     */
    public function deleteBySector(int $sectorId): bool
    {
        return $this->where('sector_id', $sectorId)->delete();
    }

    /**
     * Conta total de assentos nas filas
     */
    public function getTotalSeats(int $sectorId): int
    {
        $result = $this->selectSum('seats_count')
                       ->where('sector_id', $sectorId)
                       ->first();
        
        return (int) ($result->seats_count ?? 0);
    }
}
