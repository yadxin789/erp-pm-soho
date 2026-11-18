<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 运费模板模型
 * 按照地区和重量计算运费，支持首重和续重设置
 */
class ShippingTemplate extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id',
        'name',
        'province',
        'first_weight',
        'first_fee',
        'additional_weight',
        'additional_fee',
        'free_shipping_amount',
        'status',
    ];

    /**
     * 根据重量计算运费
     *
     * @param float $weight 重量（kg）
     * @return float 运费
     */
    public function calculateFee(float $weight): float
    {
        if (!$this->status || $weight <= 0) {
            return 0;
        }

        if ($weight <= $this->first_weight) {
            return round($this->first_fee, 2);
        }

        $additional_weight = $weight - $this->first_weight;
        $additional_units = ceil($additional_weight / $this->additional_weight);

        return round($this->first_fee + $additional_units * $this->additional_fee, 2);
    }
}