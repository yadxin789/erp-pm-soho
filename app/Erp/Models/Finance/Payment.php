<?php

namespace App\Erp\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'erp_payments';

    protected $fillable = [
        'payment_no', 'type', 'customer_id', 'supplier_id',
        'customer_invoice_id', 'supplier_bill_id',
        'payment_date', 'amount', 'method', 'reference_no', 'remark', 'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Supplier::class);
    }

    public function customerInvoice()
    {
        return $this->belongsTo(CustomerInvoice::class);
    }

    public function supplierBill()
    {
        return $this->belongsTo(SupplierBill::class);
    }
}
