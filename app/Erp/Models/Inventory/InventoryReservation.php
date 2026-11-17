<?php

namespace App\Erp\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryReservation extends Model
{
    protected $table = 'erp_inventory_reservations';

    use HasFactory;

    protected $fillable = [
        'item_id', 'warehouse_id', 'batch_number', 'quantity', 'source_document_id',
        'source_document_type', 'source_document_no', 'reservation_date', 'expiry_date',
        'status', 'remark', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Warehouse::class);
    }
}
