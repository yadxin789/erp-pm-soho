<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 货币模型
 * 管理多币种汇率，支持货币之间的转换
 */
class Currency extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'rate',
        'decimal_places',
        'is_default',
        'status',
    ];

    /**
     * 货币金额转换
     *
     * @param float $amount 金额
     * @param Currency $targetCurrency 目标货币
     * @return float 转换后的金额
     */
    public function convert(float $amount, Currency $targetCurrency): float
    {
        $baseAmount = $amount / $this->rate;

        return round($baseAmount * $targetCurrency->rate, $targetCurrency->decimal_places);
    }
}