<?php
namespace App\Soho\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncTask extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;
    
    protected $fillable = [
        'shop_id', 'type', 'status', 'params', 'result',
        'error_message', 'started_at', 'completed_at', 'operator_id'
    ];
    
    protected $casts = [
        'params' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];
    
    public function shop() { return $this->belongsTo(Shop::class); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }
}
