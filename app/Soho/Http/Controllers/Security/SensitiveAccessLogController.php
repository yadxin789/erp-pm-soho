<?php

namespace App\Soho\Http\Controllers\Security;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\SensitiveAccessLog;
use Illuminate\Http\Request;

class SensitiveAccessLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SensitiveAccessLog::query()->with('user:id,name,email');

        if ($dataType = $request->input('data_type')) {
            $query->where('data_type', $dataType);
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        $logs = $query->orderByDesc('accessed_at')->get();

        $dataTypeLabels = SensitiveAccessLog::getDataTypeLabels();
        $actionLabels = SensitiveAccessLog::getActionLabels();

        return view('security.sensitive-access-logs.index', compact('logs', 'dataTypeLabels', 'actionLabels'));
    }
}