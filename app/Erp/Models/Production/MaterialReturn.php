<?php

namespace App\Erp\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialReturn extends Model
{
    protected $table = 'erp_material_returns';

    use HasFactory;

    protected $fillable = [
        'return_no', 'return_type', 'production_order_id', 'return_date',
        'warehouse_id', 'status', 'remark', 'created_by', 'approved_by', 'approved_at',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(MaterialReturnLine::class);
    }
}
