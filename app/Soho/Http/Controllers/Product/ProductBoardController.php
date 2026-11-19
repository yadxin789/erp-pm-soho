<?php

namespace App\Soho\Http\Controllers\Product;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * 商品工作台（导入 / 审核 / 变更 / SPU 明细 / 任务队列）
 *
 * 数据源：
 * - soho_products / soho_product_skus：SPU 与 SKU
 * - soho_product_versions：版本与发布状态（审核待办与审核记录）
 * - soho_audit_product_changes：商品字段变更稽核
 * - soho_product_categories：分类
 */
class ProductBoardController extends Controller
{
    /** 商品导入（CSV 模板下载 + 上传解析入库） */
    public function import(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            ], [], ['file' => '导入文件']);

            $rows = $this->parseCsv($request->file('file')->getRealPath());
            if (! $rows) {
                return back()->with('error', '文件为空或表头不正确，请下载模板后填写。');
            }

            $imported = 0;
            $skipped = 0;
            foreach ($rows as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    $skipped++;
                    continue;
                }
                if (DB::table('soho_products')
                    ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
                    ->where('product_code', trim((string) ($row['product_code'] ?? '')))
                    ->exists()) {
                    $skipped++;
                    continue;
                }

                DB::table('soho_products')->insert([
                    'tenant_id' => $this->tenantId(),
                    'product_code' => trim((string) ($row['product_code'] ?? 'P'.now()->format('ymdHis').rand(10, 99))),
                    'name' => $name,
                    'subtitle' => trim((string) ($row['subtitle'] ?? '')) ?: null,
                    'category_id' => (int) ($row['category_id'] ?? 0) ?: null,
                    'status' => 'draft',
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                    'cost_price' => (float) ($row['cost_price'] ?? 0),
                    'retail_price' => (float) ($row['retail_price'] ?? 0),
                    'weight' => (float) ($row['weight'] ?? 0),
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $imported++;
            }

            return redirect()->route('soho.product.import')
                ->with('success', "导入完成：新增 {$imported} 条，跳过 {$skipped} 条（重复编码或名称为空）。");
        }

        $recent = DB::table('soho_products')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'product_code', 'name', 'status', 'cost_price', 'retail_price', 'created_at']);

        $categories = $this->categories();

        $stats = [
            'total' => DB::table('soho_products')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->count(),
            'draft' => DB::table('soho_products')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'draft')->count(),
            'pending' => DB::table('soho_products')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'pending')->count(),
            'active' => DB::table('soho_products')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'active')->count(),
        ];

        return view('soho.product.import', compact('recent', 'categories', 'stats'));
    }

    /** 导入模板下载 */
    public function importTemplate()
    {
        $header = ['product_code', 'name', 'subtitle', 'category_id', 'cost_price', 'retail_price', 'weight', 'description'];
        $sample = ['P9001', '示例商品', '示例副标题', '1', '12.50', '29.90', '0.35', '示例描述'];

        return response()->streamDownload(function () use ($header, $sample) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header);
            fputcsv($out, $sample);
            fclose($out);
        }, 'product-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** 待审商品（版本处于 draft 或商品待审） */
    public function reviewPending(Request $request)
    {
        $rows = DB::table('soho_product_versions as v')
            ->leftJoin('soho_products as p', 'p.id', '=', 'v.product_id')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('v.tenant_id', $t))
            ->where(fn ($q) => $q->where('v.status', 'draft')->orWhere('p.status', 'pending'))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(fn ($qq) => $qq->where('v.title', 'like', "%{$kw}%")->orWhere('p.name', 'like', "%{$kw}%")))
            ->orderByDesc('v.id')
            ->select('v.*', 'p.name as product_name', 'p.product_code', 'p.status as product_status')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'versions' => DB::table('soho_product_versions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'draft')->count(),
            'pending' => DB::table('soho_products')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'pending')->count(),
            'published' => DB::table('soho_product_versions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'published')->count(),
        ];

        return view('soho.product.review-pending', compact('rows', 'stats'));
    }

    /** 审核记录（已发布版本） */
    public function reviewRecord(Request $request)
    {
        $rows = DB::table('soho_product_versions as v')
            ->leftJoin('soho_products as p', 'p.id', '=', 'v.product_id')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('v.tenant_id', $t))
            ->where('v.status', 'published')
            ->when($request->input('search'), fn ($q, $kw) => $q->where(fn ($qq) => $qq->where('v.title', 'like', "%{$kw}%")->orWhere('p.name', 'like', "%{$kw}%")))
            ->orderByDesc('v.published_at')
            ->select('v.*', 'p.name as product_name', 'p.product_code')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'published' => DB::table('soho_product_versions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'published')->count(),
            'month' => DB::table('soho_product_versions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('published_at', '>=', now()->startOfMonth())->count(),
            'products' => DB::table('soho_product_versions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->distinct()->count('product_id'),
        ];

        return view('soho.product.review-record', compact('rows', 'stats'));
    }

    /** 变更审核（商品字段变更记录） */
    public function reviewChange(Request $request)
    {
        $rows = DB::table('soho_audit_product_changes')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('field'), fn ($q, $f) => $q->where('changed_field', $f))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(fn ($qq) => $qq->where('product_name', 'like', "%{$kw}%")->orWhere('operator', 'like', "%{$kw}%")))
            ->orderByDesc('operated_at')
            ->paginate(20)
            ->withQueryString();

        $base = DB::table('soho_audit_product_changes')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'total' => (clone $base)->count(),
            'price' => (clone $base)->where('changed_field', 'price')->count(),
            'today' => (clone $base)->where('operated_at', '>=', now()->startOfDay())->count(),
            'operators' => (clone $base)->distinct()->count('operator'),
        ];

        $fields = (clone $base)->select('changed_field')->distinct()->pluck('changed_field');

        return view('soho.product.review-change', compact('rows', 'stats', 'fields'));
    }

    /** SPU 明细（含 SKU 数量与价格区间） */
    public function spuDetail(Request $request)
    {
        $rows = DB::table('soho_products as p')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('p.tenant_id', $t))
            ->leftJoin('soho_product_skus as s', 's.product_id', '=', 'p.id')
            ->when($request->input('status'), fn ($q, $st) => $q->where('p.status', $st))
            ->when($request->input('category_id'), fn ($q, $c) => $q->where('p.category_id', $c))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(fn ($qq) => $qq->where('p.name', 'like', "%{$kw}%")->orWhere('p.product_code', 'like', "%{$kw}%")))
            ->groupBy('p.id', 'p.product_code', 'p.name', 'p.status', 'p.category_id', 'p.cost_price', 'p.retail_price', 'p.created_at')
            ->select('p.id', 'p.product_code', 'p.name', 'p.status', 'p.category_id', 'p.cost_price', 'p.retail_price', 'p.created_at')
            ->selectRaw('COUNT(s.id) as sku_count')
            ->selectRaw('MIN(s.sale_price) as min_price')
            ->selectRaw('MAX(s.sale_price) as max_price')
            ->selectRaw('SUM(s.safety_quantity) as safety_total')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'products' => DB::table('soho_products')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->count(),
            'skus' => DB::table('soho_product_skus')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->count(),
            'active' => DB::table('soho_products')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'active')->count(),
            'no_sku' => DB::table('soho_products as p')
                ->when($this->tenantId(), fn ($q, $t) => $q->where('p.tenant_id', $t))
                ->leftJoin('soho_product_skus as s', 's.product_id', '=', 'p.id')
                ->whereNull('s.id')
                ->count(),
        ];

        $categories = $this->categories();

        return view('soho.product.spu-detail', compact('rows', 'stats', 'categories'));
    }

    /** 待编辑任务（草稿 / 待审商品） */
    public function tasksPendingEdit(Request $request)
    {
        $rows = DB::table('soho_products')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->whereIn('status', ['draft', 'pending'])
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$kw}%")->orWhere('product_code', 'like', "%{$kw}%")))
            ->orderBy('status')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'draft' => DB::table('soho_products')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'draft')->count(),
            'pending' => DB::table('soho_products')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'pending')->count(),
            'no_sku' => DB::table('soho_products as p')
                ->when($this->tenantId(), fn ($q, $t) => $q->where('p.tenant_id', $t))
                ->leftJoin('soho_product_skus as s', 's.product_id', '=', 'p.id')
                ->whereNull('s.id')
                ->count(),
        ];

        return view('soho.product.tasks-pending-edit', compact('rows', 'stats'));
    }

    /** 退回任务（未发布版本） */
    public function tasksReturned(Request $request)
    {
        $rows = DB::table('soho_product_versions as v')
            ->leftJoin('soho_products as p', 'p.id', '=', 'v.product_id')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('v.tenant_id', $t))
            ->where('v.status', 'draft')
            ->whereNull('v.published_at')
            ->when($request->input('search'), fn ($q, $kw) => $q->where(fn ($qq) => $qq->where('v.title', 'like', "%{$kw}%")->orWhere('p.name', 'like', "%{$kw}%")))
            ->orderByDesc('v.updated_at')
            ->select('v.*', 'p.name as product_name', 'p.product_code')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'returned' => DB::table('soho_product_versions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'draft')->whereNull('published_at')->count(),
            'stale' => DB::table('soho_product_versions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'draft')->whereNull('published_at')->where('updated_at', '<', now()->subDays(30))->count(),
        ];

        return view('soho.product.tasks-returned', compact('rows', 'stats'));
    }

    /** 异常任务（无 SKU / 无图片 / 价格异常） */
    public function tasksException(Request $request)
    {
        $noSku = DB::table('soho_products as p')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('p.tenant_id', $t))
            ->leftJoin('soho_product_skus as s', 's.product_id', '=', 'p.id')
            ->whereNull('s.id')
            ->get(['p.id', 'p.product_code', 'p.name', 'p.status'])
            ->map(fn ($p) => (object) array_merge((array) $p, ['issue' => '缺少 SKU']));

        $noImage = DB::table('soho_products as p')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('p.tenant_id', $t))
            ->leftJoin('soho_product_images as i', 'i.product_id', '=', 'p.id')
            ->whereNull('i.id')
            ->get(['p.id', 'p.product_code', 'p.name', 'p.status'])
            ->map(fn ($p) => (object) array_merge((array) $p, ['issue' => '缺少商品图']));

        $badPrice = DB::table('soho_products')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->where(fn ($q) => $q->where('retail_price', '<=', 0)->orWhereColumn('retail_price', '<', 'cost_price'))
            ->get(['id', 'product_code', 'name', 'status'])
            ->map(fn ($p) => (object) array_merge((array) $p, ['issue' => '价格异常（售价 ≤ 0 或低于成本）']));

        $keyword = $request->input('search');
        $rows = $noSku->concat($noImage)->concat($badPrice)
            ->when($keyword, fn ($c) => $c->filter(fn ($p) => str_contains($p->name, $keyword) || str_contains((string) $p->product_code, $keyword)))
            ->values();

        $stats = [
            'no_sku' => $noSku->count(),
            'no_image' => $noImage->count(),
            'bad_price' => $badPrice->count(),
            'total' => $rows->count(),
        ];

        return view('soho.product.tasks-exception', compact('rows', 'stats', 'keyword'));
    }

    // ---------------------------------------------------------------- 内部方法

    private function tenantId(): ?int
    {
        return Auth::user()->tenant_id;
    }

    private function categories()
    {
        return DB::table('soho_product_categories')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->orderBy('sort_order')
            ->get(['id', 'name', 'parent_id'])
            ->keyBy('id');
    }

    /** @return array<int, array<string, string>> */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        $header = null;
        $rows = [];
        while (($line = fgetcsv($handle)) !== false) {
            if ($line === [null] || $line === false) {
                continue;
            }
            $line[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $line[0]);
            if ($header === null) {
                $header = array_map(fn ($h) => strtolower(trim((string) $h)), $line);
                if (! in_array('name', $header, true)) {
                    return [];
                }
                continue;
            }
            if (count($line) < count($header)) {
                $line = array_pad($line, count($header), '');
            }
            $rows[] = array_combine($header, array_slice($line, 0, count($header)));
        }
        fclose($handle);

        return $rows;
    }
}
