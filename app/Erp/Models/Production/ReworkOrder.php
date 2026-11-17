<?php

namespace App\Erp\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReworkOrder extends Model
{
    protected $table = 'erp_rework_orders';

    use HasFactory;

    protected $fillable = [
        'order_no', 'production_order_id', 'item_id', 'quantity', 'completed_quantity',
        'status', 'reason', 'remark', 'created_by', 'approved_by', 'approved_at', 'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'completed_quantity' => 'decimal:4',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }
}
