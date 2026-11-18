<?php

namespace App\Soho\Exports;

use App\Soho\Models\PlatformOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlatformOrderExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected array $orderIds;

    public function __construct(array $orderIds = [])
    {
        $this->orderIds = $orderIds;
    }

    public function collection()
    {
        $query = PlatformOrder::query();
        
        if (!empty($this->orderIds)) {
            $query->whereIn('id', $this->orderIds);
        }
        
        return $query->orderBy('order_created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            '平台订单号',
            '平台',
            '店铺',
            '订单状态',
            '支付状态',
            '订单金额',
            '支付金额',
            '买家昵称',
            '收货人',
            '收货电话',
            '收货地址',
            '下单时间',
            '支付时间',
            '发货时间',
        ];
    }

    public function map($order): array
    {
        $platformLabel = match($order->platform) {
            'taobao'    => '淘宝',
            'jd'        => '京东',
            'pinduoduo' => '拼多多',
            'amazon'    => '亚马逊',
            default     => $order->platform,
        };

        $orderStatus = match($order->order_status) {
            'pending'   => '待付款',
            'paid'      => '已付款',
            'shipped'   => '已发货',
            'completed' => '已完成',
            'cancelled' => '已取消',
            'frozen'    => '已冻结',
            default     => $order->order_status,
        };

        $payStatus = $order->pay_status === 'paid' ? '已支付' : '未支付';

        return [
            $order->platform_order_no,
            $platformLabel,
            $order->shop?->shop_name ?? '',
            $orderStatus,
            $payStatus,
            number_format($order->total_amount, 2),
            number_format($order->pay_amount, 2),
            $order->buyer_nick,
            $order->receiver_name,
            $order->receiver_phone,
            $order->receiver_address,
            $order->order_created_at,
            $order->pay_time,
            $order->ship_time,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}