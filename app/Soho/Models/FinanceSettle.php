<?php
namespace App\Soho\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceSettle extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;
    
    protected $fillable = [
        'settle_no', 'shop_id', 'amount', 'settle_method',
        'status', 'operator_id', 'remark'
    ];
    
    protected $casts = ['amount' => 'decimal:2'];
    
    public function shop() { return $this->belongsTo(Shop::class); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }
}
