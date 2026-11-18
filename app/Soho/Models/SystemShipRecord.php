<?php

namespace App\Soho\Models;

use App\Soho\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemShipRecord extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use LogsActivity;
    protected $table = 'soho_system_ship_records';

    protected $fillable = [
        'platform_order_id', 'express_company_id', 'tracking_number',
        'ship_status', 'ship_error', 'operator_id', 'ship_time',
    ];

    protected function casts(): array
    {
        return [
            'ship_time' => 'datetime',
        ];
    }

    public function platformOrder(): BelongsTo
    {
        return $this->belongsTo(PlatformOrder::class);
    }

    public function expressCompany(): BelongsTo
    {
        return $this->belongsTo(ExpressCompany::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
