<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ProgrammerActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProgrammerLogController extends Controller
{
    /**
     * Tampilkan log aktivitas programmer.
     * Bisa diakses oleh programmer dan owner.
     */
    public function index(Request $request): View
    {
        $query = ProgrammerActivityLog::with('programmer')
            ->latest('created_at');

        // Filter berdasarkan action
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        // Filter berdasarkan programmer (user)
        if ($request->filled('programmer_id')) {
            $query->where('programmer_id', $request->input('programmer_id'));
        }

        // Filter date range - dari tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        // Filter date range - sampai tanggal
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(20)->withQueryString();

        // Ambil daftar action unik untuk dropdown filter
        $actions = ProgrammerActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Ambil semua user yang pernah tercatat sebagai pelaku log.
        $programmers = User::query()
            ->whereIn('id', ProgrammerActivityLog::query()
                ->select('programmer_id')
                ->whereNotNull('programmer_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('programmer.activity_log.index', compact('logs', 'actions', 'programmers'));
    }

    /**
     * Tampilkan audit trail untuk role owner/admin.
     * Menggunakan layout owner (layouts/app) bukan layouts.programmer.
     */
    public function ownerIndex(Request $request): View
    {
        $query = ProgrammerActivityLog::with('programmer')
            ->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('programmer_id')) {
            $query->where('programmer_id', $request->input('programmer_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(20)->withQueryString();

        $actions = ProgrammerActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $programmers = User::query()
            ->whereIn('id', ProgrammerActivityLog::query()
                ->select('programmer_id')
                ->whereNotNull('programmer_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        // Pass owner module so layout/sidebar renders correctly
        return view('owner.auditTrail', compact('logs', 'actions', 'programmers'))
            ->with('module', 'owner')
            ->with('menuAuditTrail', 'active');
    }
}
