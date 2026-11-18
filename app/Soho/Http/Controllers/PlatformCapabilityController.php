<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\PlatformCapability;
use Illuminate\Http\Request;

class PlatformCapabilityController extends Controller
{
    /**
     * 平台能力列表
     */
    public function index(Request $request)
    {
        $capabilities = PlatformCapability::with(['tenant', 'platformAccount'])
            ->when($request->platform_code, function($query) use ($request) {
                $query->where('platform_code', $request->platform_code);
            })
            ->when($request->status !== null && $request->status !== '', function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('platform-capabilities.index', compact('capabilities'));
    }
}