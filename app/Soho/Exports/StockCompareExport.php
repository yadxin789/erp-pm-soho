<?php

namespace App\Soho\Exports;

use App\Soho\Models\PlatformSku;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockCompareExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return PlatformSku::query()
            ->with(['systemGoods', 'shop'])
            ->whereHas('systemGoods')
            ->orderBy('shop_id')
            ->orderBy('system_goods_id')
            ->get();
    }

    public function headings(): array
    {
        return [
            '平台',
            '店铺',
            '系统SKU',
            '商品名称',
            '系统库存',
            '平台商品ID',
            '平台SKU',
            '平台库存',
            '库存差异',
            '差异百分比',
            '最后同步时间',
        ];
    }

    public function map($platformSku): array
    {
        $systemStock = $platformSku->systemGoods->system_stock ?? 0;
        $platformStock = $platformSku->platform_stock ?? 0;
        $diff = $systemStock - $platformStock;
        $diffPercent = $systemStock > 0 ? round(($diff / $systemStock) * 100, 1) : 0;

        return [
            $platformSku->shop->platform ?? '',
            $platformSku->shop->shop_name ?? '',
            $platformSku->systemGoods->system_sku ?? '',
            $platformSku->systemGoods->goods_name ?? '',
            $systemStock,
            $platformSku->platform_goods_id,
            $platformSku->platform_sku,
            $platformStock,
            $diff,
            $diffPercent . '%',
            $platformSku->last_sync_at,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
