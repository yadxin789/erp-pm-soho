<?php
namespace App\Soho\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;
    
    protected $fillable = [
        'package_no', 'order_id', 'express_company_id', 'tracking_number',
        'weight', 'status', 'ship_time', 'operator_id', 'remark'
    ];
    
    protected $casts = [
        'ship_time' => 'datetime',
        'weight' => 'decimal:2'
    ];
    
    public function order() { return $this->belongsTo(PlatformOrder::class, 'order_id'); }
    public function expressCompany() { return $this->belongsTo(ExpressCompany::class); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }
}
