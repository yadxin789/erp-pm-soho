<?php

namespace App\Soho\Http\Controllers\Security;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\SecurityEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityEventController extends Controller
{
    public function index(Request $request)
    {
        $query = SecurityEvent::query()->with(['user:id,name,email', 'handler:id,name']);

        if ($type = $request->input('type')) {
            $query->where('event_type', $type);
        }

        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $events = $query->orderByDesc('occurred_at')->get();

        $eventTypeLabels = SecurityEvent::getEventTypeLabels();
        $severityColors = SecurityEvent::getSeverityColors();
        $statusLabels = SecurityEvent::getStatusLabels();

        return view('security.events.index', compact('events', 'eventTypeLabels', 'severityColors', 'statusLabels'));
    }

    public function resolve(Request $request, SecurityEvent $event)
    {
        $request->validate(['handle_note' => 'nullable|string|max:500']);

        $event->update([
            'status' => 'resolved',
            'handled_by' => Auth::id(),
            'handled_at' => now(),
            'handle_note' => $request->input('handle_note'),
        ]);

        return back()->with('success', '安全事件已标记为解决');
    }
}