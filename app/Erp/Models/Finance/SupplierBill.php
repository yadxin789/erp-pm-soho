<?php

namespace App\Erp\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierBill extends Model
{
    protected $table = 'erp_supplier_bills';

    use SoftDeletes;

    protected $fillable = [
        'bill_no', 'supplier_id', 'purchase_order_id', 'bill_date', 'due_date',
        'subtotal', 'tax_amount', 'total_amount', 'paid_amount', 'status', 'remark', 'created_by',
    ];

    public function supplier()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(\App\Erp\Models\Purchasing\PurchaseOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
