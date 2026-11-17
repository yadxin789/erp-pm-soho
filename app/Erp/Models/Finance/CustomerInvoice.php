<?php

namespace App\Erp\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInvoice extends Model
{
    protected $table = 'erp_customer_invoices';

    use SoftDeletes;

    protected $fillable = [
        'invoice_no', 'customer_id', 'sales_order_id', 'invoice_date', 'due_date',
        'subtotal', 'tax_amount', 'total_amount', 'paid_amount', 'status', 'remark', 'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(\App\Erp\Models\Sales\SalesOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
