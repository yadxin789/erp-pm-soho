<?php

namespace App\Erp\Models\Inventory;

use App\Erp\Enums\InventoryTransactionType;
use App\Erp\Enums\InventoryQualityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $table = 'erp_inventory_transactions';

    use HasFactory;

    protected $fillable = [
        'transaction_no', 'transaction_type', 'item_id', 'warehouse_id', 'location_id',
        'batch_number', 'serial_number', 'quantity', 'quality_status',
        'source_document_id', 'source_document_type', 'source_document_line_id',
        'related_transaction_id', 'remark', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'transaction_type' => InventoryTransactionType::class,
        'quality_status' => InventoryQualityStatus::class,
    ];

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Warehouse::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Location::class);
    }

    public function relatedTransaction()
    {
        return $this->belongsTo(InventoryTransaction::class, 'related_transaction_id');
    }
}
