<?php

namespace App\Erp\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model
{
    protected $table = 'erp_purchase_requisitions';

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'req_no', 'source_type', 'source_id', 'status', 'remark',
        'requisition_date', 'warehouse_id',
        'created_by', 'approved_by', 'approved_at', 'updated_by',
    ];

    protected $casts = [
        'requisition_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(PurchaseRequisitionLine::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Warehouse::class);
    }
}
