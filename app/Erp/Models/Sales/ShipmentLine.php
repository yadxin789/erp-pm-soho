<?php

namespace App\Erp\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentLine extends Model
{
    protected $table = 'erp_shipment_lines';

    use HasFactory;

    protected $fillable = [
        'shipment_id', 'line_number', 'item_id', 'quantity', 'sales_order_line_id',
        'batch_number', 'serial_number', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }

    public function salesOrderLine()
    {
        return $this->belongsTo(SalesOrderLine::class);
    }
}
