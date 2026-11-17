<?php

namespace App\Erp\Models\Quality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefectRecord extends Model
{
    protected $table = 'erp_defect_records';

    use HasFactory;

    protected $fillable = [
        'record_no', 'defect_type', 'defect_code_id', 'production_order_id',
        'item_id', 'batch_number', 'quantity', 'disposition', 'description',
        'root_cause', 'corrective_action', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function defectCode()
    {
        return $this->belongsTo(DefectCode::class);
    }

    public function productionOrder()
    {
        return $this->belongsTo(\App\Erp\Models\Production\ProductionOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }
}
