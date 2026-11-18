<?php

namespace App\Soho\Exports;

use App\Soho\Models\SystemGoods;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return SystemGoods::query()
            ->with(['platformSkus' => function ($q) {
                $q->with('shop');
            }])
            ->orderBy('system_sku')
            ->get();
    }

    public function headings(): array
    {
        return [
            '系统SKU',
            '商品名称',
            '系统库存',
            '最低库存预警',
            '售价',
            '绑定平台',
            '平台商品ID',
            '平台SKU',
            '平台库存',
            '是否预警',
        ];
    }

    public function map($goods): array
    {
        $platformSkus = $goods->platformSkus;
        $isWarning = $goods->min_stock_warn > 0 && $goods->system_stock <= $goods->min_stock_warn;

        $platformInfo = '';
        $platformGoodsIds = '';
        $platformSkusStr = '';
        $platformStocks = '';

        if ($platformSkus->isNotEmpty()) {
            foreach ($platformSkus as $sku) {
                $platformInfo .= ($sku->shop->platform ?? '') . ', ';
                $platformGoodsIds .= ($sku->platform_goods_id ?? '') . ', ';
                $platformSkusStr .= ($sku->platform_sku ?? '') . ', ';
                $platformStocks .= ($sku->platform_stock ?? '-') . ', ';
            }
            $platformInfo = rtrim($platformInfo, ', ');
            $platformGoodsIds = rtrim($platformGoodsIds, ', ');
            $platformSkusStr = rtrim($platformSkusStr, ', ');
            $platformStocks = rtrim($platformStocks, ', ');
        }

        return [
            $goods->system_sku,
            $goods->goods_name,
            $goods->system_stock,
            $goods->min_stock_warn,
            number_format($goods->selling_price, 2),
            $platformInfo,
            $platformGoodsIds,
            $platformSkusStr,
            $platformStocks,
            $isWarning ? '是' : '否',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
