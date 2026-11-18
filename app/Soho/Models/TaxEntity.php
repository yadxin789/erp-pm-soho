<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 税务主体模型
 * 管理企业税务登记信息，支持多主体、多地区的税务合规管理
 */
class TaxEntity extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'tax_no', 'country', 'region', 'city',
        'tax_type', 'legal_person', 'contact_name', 'contact_phone', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}