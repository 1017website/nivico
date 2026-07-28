<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntegrationLog;
use Illuminate\Http\Request;

class IntegrationLogController extends Controller
{
    public function index(Request $request)
    {
        $query = IntegrationLog::with('order')->latest();

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->where(function ($search) use ($term) {
                $search->where('order_number', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhere('recipient', 'like', "%{$term}%")
                    ->orWhere('message', 'like', "%{$term}%");
            });
        }

        $logs = $query->paginate(40)->withQueryString();
        $today = now()->startOfDay();
        $stats = [
            'today' => IntegrationLog::where('created_at', '>=', $today)->count(),
            'emails' => IntegrationLog::where('channel', 'email')->where('created_at', '>=', $today)->count(),
            'callbacks' => IntegrationLog::where('channel', 'duitku')->where('created_at', '>=', $today)->count(),
            'problems' => IntegrationLog::whereIn('status', ['failed', 'rejected'])
                ->where('created_at', '>=', $today)
                ->count(),
        ];

        return view('admin.integration-logs.index', compact('logs', 'stats'));
    }

    public function show(IntegrationLog $integrationLog)
    {
        $integrationLog->load('order');

        return view('admin.integration-logs.show', compact('integrationLog'));
    }
}
