<?php

namespace App\Erp\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionPlanLine extends Model
{
    protected $table = 'erp_production_plan_lines';

    use HasFactory;

    protected $fillable = [
        'production_plan_id', 'line_number', 'item_id', 'quantity', 'plan_date',
        'work_center_id', 'sales_order_id', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function productionPlan()
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }

    public function workCenter()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\WorkCenter::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(\App\Erp\Models\Sales\SalesOrder::class);
    }
}
