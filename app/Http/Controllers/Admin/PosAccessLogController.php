<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosAccessLog;
use Illuminate\Http\Request;

class PosAccessLogController extends Controller
{
    public function index()
    {
        $posAccessLogs = PosAccessLog::where('access_type', 'pos')
            ->whereIn('action', ['login', 'logout'])
            ->with('user')
            ->latest()
            ->get();

        $paymentManagerLogs = PosAccessLog::where('access_type', 'payment_manager')
            ->whereIn('action', ['login', 'logout'])
            ->with('user')
            ->latest()
            ->get();

        $posOrderLogs = PosAccessLog::where('access_type', 'pos')
            ->whereIn('action', ['order'])
            ->with('user')
            ->latest()
            ->get();

        $paymentLogs = PosAccessLog::where('access_type', 'payment_manager')
            ->whereIn('action', ['payment'])
            ->with('user')
            ->latest()
            ->get();

        return view('admin.pos-access-logs', compact(
            'posAccessLogs',
            'paymentManagerLogs',
            'posOrderLogs',
            'paymentLogs'
        ));
    }
} 