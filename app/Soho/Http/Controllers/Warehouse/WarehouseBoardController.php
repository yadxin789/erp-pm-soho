<?php

namespace App\Soho\Http\Controllers\Warehouse;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 仓储看板（库位 / 安全库存 / 采购入库 / 库存单据 / 库龄）
 *
 * 数据源全部为 SOHO 自有表，按 tenant_id 隔离：
 * - soho_warehouses / soho_warehouse_locations：仓库与库位
 * - soho_safe_stocks：安全库存预警
 * - soho_procurement_inbounds：采购到货入库
 * - soho_inventory_documents：出入库 / 调拨 / 盘点单据
 * - soho_inventory_batches：批次库存（库龄、效期）
 */
class WarehouseBoardController extends Controller
{
    /** 库区（按 zone_name 归并库位） */
    public function zones()
    {
        $rows = $this->locationsQuery()
            ->select('zone_name', 'warehouse_id')
            ->selectRaw('COUNT(*) as location_count')
            ->selectRaw('SUM(capacity) as capacity_total')
            ->selectRaw('SUM(current_usage) as usage_total')
            ->selectRaw("SUM(CASE WHEN status = '1' OR status = 1 THEN 1 ELSE 0 END) as active_count")
            ->groupBy('zone_name', 'warehouse_id')
            ->orderBy('warehouse_id')
            ->orderBy('zone_name')
            ->get();

        $warehouses = $this->warehouses();

        $stats = [
            'zones' => $rows->count(),
            'locations' => $this->locationsQuery()->count(),
            'usage' => (float) $this->locationsQuery()->sum('current_usage'),
            'capacity' => (float) $this->locationsQuery()->sum('capacity'),
        ];

        return view('soho.warehouse.zones', compact('rows', 'warehouses', 'stats'));
    }

    /** 库位列表 */
    public function locations(Request $request)
    {
        $rows = $this->locationsQuery()
            ->when($request->input('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->input('zone'), fn ($q, $zone) => $q->where('zone_name', $zone))
            ->when($request->input('search'), fn ($q, $kw) => $q->where('location_code', 'like', "%{$kw}%"))
            ->orderBy('warehouse_id')
            ->orderBy('zone_name')
            ->orderBy('location_code')
            ->paginate(20)
            ->withQueryString();

        $warehouses = $this->warehouses();
        $zones = $this->locationsQuery()->select('zone_name')->distinct()->orderBy('zone_name')->pluck('zone_name');

        return view('soho.warehouse.locations', compact('rows', 'warehouses', 'zones'));
    }

    /** 安全库存预警 */
    public function stockWarning(Request $request)
    {
        $level = $request->input('level');

        $rows = DB::table('soho_safe_stocks')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($level, fn ($q, $lv) => $q->where('alert_level', $lv))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(fn ($qq) => $qq->where('sku_code', 'like', "%{$kw}%")->orWhere('product_name', 'like', "%{$kw}%")))
            ->when($request->input('only_short') === '1', fn ($q) => $q->whereColumn('current_stock', '<', 'safe_stock'))
            ->orderByRaw("FIELD(alert_level,'danger','warning','normal')")
            ->orderBy('current_stock')
            ->paginate(20)
            ->withQueryString();

        $base = DB::table('soho_safe_stocks')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'total' => (clone $base)->count(),
            'danger' => (clone $base)->where('alert_level', 'danger')->count(),
            'warning' => (clone $base)->where('alert_level', 'warning')->count(),
            'short' => (clone $base)->whereColumn('current_stock', '<', 'safe_stock')->count(),
        ];

        return view('soho.warehouse.stock-warning', compact('rows', 'stats', 'level'));
    }

    /** 采购入库 */
    public function inbound(Request $request)
    {
        $rows = DB::table('soho_procurement_inbounds')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(fn ($qq) => $qq->where('inbound_no', 'like', "%{$kw}%")->orWhere('arrival_no', 'like', "%{$kw}%")))
            ->orderByDesc('inbound_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $warehouses = $this->warehouses();

        $base = DB::table('soho_procurement_inbounds')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->whereIn('status', ['pending', 'processing'])->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'quantity' => (float) (clone $base)->where('status', 'completed')->sum('quantity'),
        ];

        return view('soho.warehouse.inbound', compact('rows', 'warehouses', 'stats'));
    }

    /** 出库单（document_type=outbound 的库存单据视图） */
    public function outboundDocs(Request $request)
    {
        return $this->docsByType($request, 'outbound', '出库单');
    }

    /** 盘点单（document_type=stocktake） */
    public function stocktakeDocs(Request $request)
    {
        return $this->docsByType($request, 'stocktake', '盘点单');
    }

    /** 库存调整申请（document_type=inbound/outbound 之外的调整单 + 审批队列） */
    public function adjustDocs(Request $request)
    {
        $approvals = DB::table('soho_inventory_adjust_approvals')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'approval_page')
            ->withQueryString();

        return $this->docsByType($request, 'adjust', '库存调整申请', $approvals);
    }

    /** 退货收货（soho_return_shipments） */
    public function returnReceive(Request $request)
    {
        $rows = DB::table('soho_return_shipments')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(function ($qq) use ($kw) {
                $qq->where('return_no', 'like', "%{$kw}%")
                    ->orWhere('tracking_no', 'like', "%{$kw}%")
                    ->orWhere('receiver_name', 'like', "%{$kw}%");
            }))
            ->orderByRaw("FIELD(status,'shipped','in_transit','received')")
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $base = DB::table('soho_return_shipments')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'total' => (clone $base)->count(),
            'in_transit' => (clone $base)->whereIn('status', ['shipped', 'in_transit'])->count(),
            'received' => (clone $base)->where('status', 'received')->count(),
        ];

        $warehouses = $this->warehouses();

        return view('soho.warehouse.return-receive', compact('rows', 'stats', 'warehouses'));
    }

    /** 退货质检（soho_return_inspections + 已收货待质检退货） */
    public function returnInspect(Request $request)
    {
        $rows = DB::table('soho_return_inspections')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('result'), fn ($q, $r) => $q->where('result', $r))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $pending = DB::table('soho_return_shipments')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('status', 'received')
            ->orderByDesc('received_at')
            ->get(['id', 'return_no', 'order_id', 'received_at', 'warehouse_id']);

        $stats = [
            'total' => DB::table('soho_return_inspections')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->count(),
            'pending' => $pending->count(),
            'qualified' => DB::table('soho_return_inspections')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('result', 'qualified')->count(),
            'unqualified' => DB::table('soho_return_inspections')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('result', 'unqualified')->count(),
        ];

        return view('soho.warehouse.return-inspect', compact('rows', 'pending', 'stats'));
    }

    /** 库存单据（出入库 / 调拨 / 盘点） */
    public function inventoryDoc(Request $request)
    {
        return $this->docsByType($request, null, '库存单据');
    }

    private function docsByType(Request $request, ?string $fixedType, string $title, $approvals = null)
    {
        $rows = DB::table('soho_inventory_documents')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($fixedType, fn ($q, $type) => $q->where('document_type', $type))
            ->when(! $fixedType && $request->input('type'), fn ($q, $type) => $q->where('document_type', $type))
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->input('search'), fn ($q, $kw) => $q->where('document_no', 'like', "%{$kw}%"))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $warehouses = $this->warehouses();

        $base = DB::table('soho_inventory_documents')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($fixedType, fn ($q, $type) => $q->where('document_type', $type));

        $stats = [
            'total' => (clone $base)->count(),
            'draft' => (clone $base)->whereIn('status', ['draft', 'pending'])->count(),
            'executing' => (clone $base)->where('status', 'executing')->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
        ];

        $types = DB::table('soho_inventory_documents')->select('document_type')->distinct()->pluck('document_type');

        return view('soho.warehouse.inventory-doc', compact('rows', 'warehouses', 'stats', 'types', 'fixedType', 'title', 'approvals'));
    }

    /** 库龄分析（按批次入库时间分档） */
    public function stockAge(Request $request)
    {
        $rows = $this->batchesQuery()
            ->when($request->input('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->orderBy('created_at')
            ->paginate(20)
            ->withQueryString();

        $all = $this->batchesQuery()->get(['quantity', 'available_quantity', 'total_cost', 'created_at', 'expiry_date']);

        $ages = ['0-30' => 0, '31-90' => 0, '91-180' => 0, '180+' => 0];
        $expiring = 0;
        $value = 0.0;
        foreach ($all as $batch) {
            $days = $batch->created_at ? now()->diffInDays($batch->created_at) : 0;
            $key = $days <= 30 ? '0-30' : ($days <= 90 ? '31-90' : ($days <= 180 ? '91-180' : '180+'));
            $ages[$key]++;
            $value += (float) $batch->total_cost;
            if ($batch->expiry_date && now()->diffInDays($batch->expiry_date, false) <= 60) {
                $expiring++;
            }
        }

        $stats = [
            'batches' => $all->count(),
            'quantity' => (float) $all->sum('quantity'),
            'value' => $value,
            'expiring' => $expiring,
        ];

        $warehouses = $this->warehouses();

        return view('soho.warehouse.stock-age', compact('rows', 'stats', 'ages', 'warehouses'));
    }

    /** 库存周转（按出库流水估算：出库量 / 平均库存） */
    public function inventoryTurnover(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $since = now()->subDays($days);

        $outbound = DB::table('soho_inventory_flows')
            ->where('type', 'out')
            ->where('created_at', '>=', $since)
            ->select('sku_id')
            ->selectRaw('SUM(quantity) as out_qty')
            ->groupBy('sku_id')
            ->pluck('out_qty', 'sku_id');

        $stock = DB::table('soho_inventory_batches')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->select('sku_id')
            ->selectRaw('SUM(quantity) as stock_qty')
            ->selectRaw('SUM(available_quantity) as available_qty')
            ->groupBy('sku_id')
            ->get();

        $rows = $stock->map(function ($row) use ($outbound, $days) {
            $row->out_qty = (float) ($outbound[$row->sku_id] ?? 0);
            $row->turnover = $row->stock_qty > 0 ? round($row->out_qty / $row->stock_qty, 2) : 0;
            $row->turnover_days = $row->out_qty > 0 ? (int) round($row->stock_qty / ($row->out_qty / $days)) : null;
            return $row;
        })->sortByDesc('turnover')->values();

        $stats = [
            'skus' => $rows->count(),
            'out_qty' => $rows->sum('out_qty'),
            'stock_qty' => $rows->sum('stock_qty'),
            'slow' => $rows->where('turnover', '<=', 0)->count(),
        ];

        return view('soho.warehouse.inventory-turnover', compact('rows', 'stats', 'days'));
    }

    // ---------------------------------------------------------------- 内部方法

    private function tenantId(): ?int
    {
        return Auth::user()->tenant_id;
    }

    private function warehouses()
    {
        return DB::table('soho_warehouses')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->orderBy('priority')
            ->get(['id', 'name', 'code', 'status'])
            ->keyBy('id');
    }

    private function locationsQuery()
    {
        return DB::table('soho_warehouse_locations')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
    }

    private function batchesQuery()
    {
        return DB::table('soho_inventory_batches')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
    }
}
