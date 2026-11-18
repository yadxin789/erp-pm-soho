<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformApiLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $table = 'soho_platform_api_logs';

    protected $fillable = [
        'platform', 'shop_id', 'api_method', 'request_url', 'request_params',
        'response_data', 'response_status', 'error_message',
        'response_time_ms', 'trace_id', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'request_params' => 'array',
            'response_data'  => 'array',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
