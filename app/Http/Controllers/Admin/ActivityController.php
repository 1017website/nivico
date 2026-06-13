<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('q')) {
            $query->where(function ($w) use ($request) {
                $w->where('user_name', 'like', '%'.$request->q.'%')
                  ->orWhere('description', 'like', '%'.$request->q.'%');
            });
        }

        $logs = $query->paginate(30)->withQueryString();
        return view('admin.activity.index', compact('logs'));
    }
}
