<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\OperationLog;
use Illuminate\Http\Request;

class OperationLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = OperationLog::with('user')
            ->when($request->search, function($query) use ($request) {
                $query->where('description', 'like', "%{$request->search}%");
            })
            ->when($request->user_id, function($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when($request->action, function($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_end);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('operation-logs.index', compact('logs'));
    }
}