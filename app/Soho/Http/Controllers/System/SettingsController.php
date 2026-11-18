<?php

namespace App\Soho\Http\Controllers\System;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        $configs = SystemConfig::pluck('value', 'key')->toArray();
        return view('system.settings', compact('configs'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'auto_sync_orders' => 'boolean',
            'auto_sync_stock' => 'boolean',
            'order_sync_interval' => 'nullable|integer|min:1',
            'stock_sync_interval' => 'nullable|integer|min:1',
        ]);

        foreach ($validated as $key => $value) {
            SystemConfig::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // 清除缓存
        Cache::forget('soho_system_configs');

        return back()->with('success', '系统设置已更新');
    }
}