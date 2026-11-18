<?php

namespace App\Soho\Exports;

use App\Soho\Models\PlatformOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected ?string $startDate;
    protected ?string $endDate;
    protected ?string $platform;
    protected ?int $shopId;

    public function __construct(?string $startDate = null, ?string $endDate = null, ?string $platform = null, ?int $shopId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->platform = $platform;
        $this->shopId = $shopId;
    }

    public function collection()
    {
        $query = PlatformOrder::query()
            ->whereIn('order_status', ['paid', 'shipped', 'completed'])
            ->when($this->startDate, fn($q) => $q->where('order_created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->where('order_created_at', '<=', $this->endDate . ' 23:59:59'))
            ->when($this->platform, fn($q) => $q->where('platform', $this->platform))
            ->when($this->shopId, fn($q) => $q->where('shop_id', $this->shopId));

        return $query->orderBy('order_created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            '平台订单号',
            '平台',
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
        return [
            $order->platform_order_no,
            $order->platform,
            $this->mapStatus($order->order_status),
            $order->pay_status === 'paid' ? '已支付' : '未支付',
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

    protected function mapStatus(string $status): string
    {
        return match ($status) {
            'pending'   => '待付款',
            'paid'      => '已付款',
            'shipped'   => '已发货',
            'completed' => '已完成',
            'cancelled' => '已取消',
            default     => $status,
        };
    }
}
