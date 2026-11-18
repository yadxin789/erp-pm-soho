<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 商品版本模型
 * 记录商品（SPU）的版本历史，支持商品内容的版本控制、审核和发布
 */
class ProductVersion extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'product_id', 'version_no', 'title', 'description',
        'price', 'status', 'published_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'published_at' => 'datetime',
            'version_no' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}