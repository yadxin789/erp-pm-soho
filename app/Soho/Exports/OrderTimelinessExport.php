<?php

namespace App\Soho\Exports;

use App\Soho\Models\PlatformOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderTimelinessExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected ?string $startDate;
    protected ?string $endDate;
    protected ?string $platform;

    public function __construct(?string $startDate = null, ?string $endDate = null, ?string $platform = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->platform = $platform;
    }

    public function collection()
    {
        return PlatformOrder::query()
            ->whereIn('order_status', ['paid', 'shipped', 'completed'])
            ->when($this->startDate, fn($q) => $q->where('order_created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->where('order_created_at', '<=', $this->endDate . ' 23:59:59'))
            ->when($this->platform, fn($q) => $q->where('platform', $this->platform))
            ->orderBy('order_created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            '平台订单号',
            '平台',
            '订单状态',
            '下单时间',
            '支付时间',
            '发货时间',
            '付款耗时(分钟)',
            '发货耗时(分钟)',
            '总处理耗时(分钟)',
            '买家昵称',
        ];
    }

    public function map($order): array
    {
        $orderCreated = $order->order_created_at ? strtotime($order->order_created_at) : null;
        $payTime = $order->pay_time ? strtotime($order->pay_time) : null;
        $shipTime = $order->ship_time ? strtotime($order->ship_time) : null;

        $payDuration = ($orderCreated && $payTime) ? round(($payTime - $orderCreated) / 60, 0) : '-';
        $shipDuration = ($payTime && $shipTime) ? round(($shipTime - $payTime) / 60, 0) : '-';
        $totalDuration = ($orderCreated && $shipTime) ? round(($shipTime - $orderCreated) / 60, 0) : '-';

        return [
            $order->platform_order_no,
            $order->platform,
            $this->mapStatus($order->order_status),
            $order->order_created_at,
            $order->pay_time,
            $order->ship_time,
            $payDuration,
            $shipDuration,
            $totalDuration,
            $order->buyer_nick,
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
