<?php

namespace App\Http\Controllers\Registry;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-registry');

        $logs = AuditLog::query()->with('user')->latest()->paginate(40);

        return view('registry.audit.index', compact('logs'));
    }
}
