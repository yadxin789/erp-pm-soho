<?php

namespace App\Soho\Http\Controllers\Api\Logistics;

use App\Soho\Models\LogisticsCompany;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 物流API控制器
 */
class LogisticsController extends Controller
{
    /**
     * 获取物流公司列表
     */
    public function companies(Request $request)
    {
        $companies = LogisticsCompany::query()
            ->where('status', 'active');

        if ($request->has('is_international')) {
            $companies->where('is_international', $request->is_international);
        }

        return response()->json([
            'data' => $companies->paginate($request->per_page ?? 20),
        ]);
    }

    /**
     * 查询物流轨迹
     */
    public function track(Request $request, string $trackingNumber)
    {
        $request->validate([
            'company_code' => 'nullable|string|max:20',
        ]);

        $tracks = \App\Soho\Models\LogisticsTrack::where('tracking_number', $trackingNumber)
            ->when($request->company_code, fn($q, $code) => $q->where('company_code', $code))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $tracks,
            'tracking_number' => $trackingNumber,
        ]);
    }
}