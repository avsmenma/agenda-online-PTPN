<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppNotificationLog;
use Illuminate\Http\Request;

class WhatsAppNotificationLogController extends Controller
{
    public function index(Request $request)
    {
        $query = WhatsAppNotificationLog::query()->with(['dokumen', 'user'])->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('channel')) {
            $query->where('channel', (string) $request->input('channel'));
        }

        if ($request->filled('role_code')) {
            $query->where('role_code', (string) $request->input('role_code'));
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('phone_number', 'like', "%{$q}%")
                    ->orWhere('message_type', 'like', "%{$q}%")
                    ->orWhere('role_code', 'like', "%{$q}%")
                    ->orWhere('fallback_reason', 'like', "%{$q}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('notifications.whatsapp_logs.index', [
            'logs' => $logs,
        ]);
    }
}

