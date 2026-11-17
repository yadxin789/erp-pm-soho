<?php

namespace App\Erp\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    protected $table = 'erp_customers';

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'short_name', 'contact_person', 'phone', 'email', 'address',
        'tax_id', 'payment_terms', 'credit_limit', 'active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:4',
        'active' => 'boolean',
    ];

    public function salesOrders()
    {
        return $this->hasMany(\App\Erp\Models\Sales\SalesOrder::class);
    }

    public function quotations()
    {
        return $this->hasMany(\App\Erp\Models\Sales\Quotation::class);
    }

    public function shipments()
    {
        return $this->hasMany(\App\Erp\Models\Sales\Shipment::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(\App\Erp\Models\Sales\SalesReturn::class);
    }
}
