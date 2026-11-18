<?php
namespace App\Soho\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceReconcile extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;
    
    protected $fillable = [
        'reconcile_no', 'shop_id', 'period_start', 'period_end',
        'amount', 'status', 'operator_id', 'remark'
    ];
    
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'amount' => 'decimal:2'
    ];
    
    public function shop() { return $this->belongsTo(Shop::class); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }
}
