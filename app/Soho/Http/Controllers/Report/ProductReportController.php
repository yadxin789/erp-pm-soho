<?php
namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductReportController extends Controller
{
    /**
     * 商品报表
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // 日期范围筛选
        if ($request->date_start) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        $products = $query->withCount(['skus'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'inactive' => Product::where('status', 'inactive')->count(),
        ];

        return view('report.product', compact('products', 'stats'));
    }

    /**
     * 商品报表导出
     */
    public function export(Request $request)
    {
        $query = Product::query();

        // 日期范围筛选
        if ($request->date_start) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        $products = $query->withCount(['skus'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'product_report_' . date('YmdHis') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];

        $callback = function() use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', '商品名称', '状态', 'SKU数量', '创建时间']);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->id,
                    $product->name ?? '',
                    $product->status ?? '',
                    $product->skus_count ?? 0,
                    $product->created_at,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
