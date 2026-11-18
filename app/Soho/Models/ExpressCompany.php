<?php

namespace App\Soho\Models;

use App\Soho\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ExpressCompany extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use LogsActivity;
    protected $table = 'soho_express_company';

    protected $fillable = [
        'name', 'code', 'taobao_code', 'jd_code', 'pdd_code', 'amazon_code',
        'website', 'phone', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
