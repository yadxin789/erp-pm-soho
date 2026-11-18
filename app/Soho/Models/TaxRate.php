<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 税率模型
 * 管理各地区的税率设置，支持含税价与不含税价计算
 */
class TaxRate extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id',
        'name',
        'country_code',
        'province',
        'rate',
        'is_compound',
        'included_in_price',
        'priority',
        'status',
    ];

    /**
     * 计算税额
     *
     * @param float $amount 金额
     * @return float 税额
     */
    public function calculateTax(float $amount): float
    {
        if (!$this->status) {
            return 0;
        }

        $rate = $this->rate / 100;

        if ($this->included_in_price) {
            return round($amount - ($amount / (1 + $rate)), 2);
        }

        return round($amount * $rate, 2);
    }
}