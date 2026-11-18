<?php

namespace App\Soho\Exports;

use App\Soho\Models\SystemGoods;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SystemGoodsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected array $goodsIds;

    public function __construct(array $goodsIds = [])
    {
        $this->goodsIds = $goodsIds;
    }

    public function collection()
    {
        $query = SystemGoods::query();
        
        if (!empty($this->goodsIds)) {
            $query->whereIn('id', $this->goodsIds);
        }
        
        return $query->orderBy('updated_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            '商品名称',
            '系统SKU',
            '商品条码',
            '分类',
            '品牌',
            '成本价',
            '销售价',
            '系统库存',
            '最低库存预警',
            '单位',
            '状态',
            '创建时间',
            '更新时间',
        ];
    }

    public function map($goods): array
    {
        return [
            $goods->goods_name,
            $goods->system_sku,
            $goods->barcode ?? '',
            $goods->category ?? '',
            $goods->brand ?? '',
            number_format($goods->cost_price, 2),
            number_format($goods->selling_price, 2),
            $goods->system_stock,
            $goods->min_stock_warn,
            $goods->unit ?? '',
            $goods->status ? '上架' : '下架',
            $goods->created_at,
            $goods->updated_at,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}