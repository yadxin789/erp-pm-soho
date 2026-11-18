<?php

namespace App\Soho\Http\Controllers\Log;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\SyncTaskLog;
use Illuminate\Http\Request;

class SyncTaskLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = SyncTaskLog::when($request->task_type, function($query) use ($request) {
                $query->where('task_type', $request->task_type);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_end);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('log.sync-task', compact('logs'));
    }
}