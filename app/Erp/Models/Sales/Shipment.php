<?php

namespace App\Erp\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    protected $table = 'erp_shipments';

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_no', 'customer_id', 'sales_order_id', 'shipment_date', 'warehouse_id',
        'status', 'remark', 'created_by', 'approved_by', 'approved_at',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(ShipmentLine::class);
    }
}
