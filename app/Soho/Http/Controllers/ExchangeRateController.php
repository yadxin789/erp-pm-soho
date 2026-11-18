<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ExchangeRate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    /**
     * 汇率管理列表
     */
    public function index(Request $request)
    {
        $exchangeRates = ExchangeRate::when($request->from_currency, function($query) use ($request) {
            $query->where('from_currency', $request->from_currency);
        })
        ->when($request->to_currency, function($query) use ($request) {
            $query->where('to_currency', $request->to_currency);
        })
        ->orderBy('updated_at', 'desc')
        ->paginate(20);

        return view('exchange-rates.index', compact('exchangeRates'));
    }

    /**
     * 保存汇率
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_currency' => 'required|string|max:10',
            'to_currency' => 'required|string|max:10',
            'rate' => 'required|numeric|min:0',
            'effective_date' => 'nullable|date',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id ?? null;

        ExchangeRate::updateOrCreate(
            [
                'from_currency' => $validated['from_currency'],
                'to_currency' => $validated['to_currency'],
            ],
            $validated
        );

        return redirect()->route('soho.exchange_rates.index')->with('success', '汇率更新成功');
    }
}