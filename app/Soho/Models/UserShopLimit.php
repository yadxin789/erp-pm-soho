<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserShopLimit extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $table = 'soho_user_shop_limit';

    protected $fillable = [
        'user_id', 'shop_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
