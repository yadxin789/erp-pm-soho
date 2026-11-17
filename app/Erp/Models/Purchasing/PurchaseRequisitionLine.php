<?php

namespace App\Erp\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionLine extends Model
{
    protected $table = 'erp_purchase_requisition_lines';

    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id', 'line_number', 'item_id', 'quantity',
        'ordered_quantity', 'required_date', 'supplier_id', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'ordered_quantity' => 'decimal:4',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Supplier::class);
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->ordered_quantity;
    }
}
