<?php

namespace App\Erp\Models\Logistics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticsTrace extends Model
{
    protected $table = 'erp_logistics_traces';

    protected $fillable = ['shipment_id', 'traced_at', 'status', 'description', 'location'];

    protected $casts = [
        'traced_at' => 'datetime',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(LogisticsShipment::class);
    }
}
