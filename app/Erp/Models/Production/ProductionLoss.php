<?php

namespace App\Erp\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLoss extends Model
{
    protected $table = 'erp_production_losses';

    use HasFactory;

    protected $fillable = [
        'loss_no', 'production_order_id', 'item_id', 'quantity', 'loss_type',
        'reason', 'remark', 'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
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
