<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\SearchPreset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdvancedSearchController extends Controller
{
    /**
     * Advanced search with multiple filters
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = Dokumen::query();

            // Apply role-specific filter (Team Verifikasi only sees their docs)
            $userRole = auth()->user()->role ?? 'team_verifikasi';
            if ($userRole === 'team_verifikasi') {
                $query->where(function ($q) {
                    $q->where('current_handler', 'team_verifikasi')
                        ->orWhereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi']);
                });
            }

            // Full-text search
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_spp', 'LIKE', "%{$search}%")
                        ->orWhere('nomor_agenda', 'LIKE', "%{$search}%")
                        ->orWhere('uraian', 'LIKE', "%{$search}%")
                        ->orWhere('dibayar_kepada', 'LIKE', "%{$search}%");
                });
            }

            // Filter by Tahun
            if ($request->filled('tahun')) {
                $tahunArray = is_array($request->tahun) ? $request->tahun : [$request->tahun];
                $query->whereIn(DB::raw('YEAR(created_at)'), $tahunArray);
            }

            // Filter by Status (multiple)
            if ($request->filled('status')) {
                $statusArray = is_array($request->status) ? $request->status : [$request->status];
                $query->whereIn('status', $statusArray);
            }

            // Filter by Bagian
            if ($request->filled('bagian')) {
                $bagianArray = is_array($request->bagian) ? $request->bagian : [$request->bagian];
                $query->whereIn('bagian_id', $bagianArray);
            }

            // Filter by Nilai Range
            if ($request->filled('nilai_min')) {
                $query->where('nilai_rupiah', '>=', $request->nilai_min);
            }
            if ($request->filled('nilai_max')) {
                $query->where('nilai_rupiah', '<=', $request->nilai_max);
            }

            // Filter by Deadline (based on received_at in role_data)
            if ($request->filled('deadline')) {
                $deadline = $request->deadline;

                $query->whereHas('roleData', function ($q) use ($deadline, $userRole) {
                    $q->where('role_code', $userRole);

                    if ($deadline === 'aman') {
                        // Less than 24 hours ago
                        $q->where('received_at', '>', now()->subDay());
                    } elseif ($deadline === 'peringatan') {
                        // Between 1-3 days ago
                        $q->whereBetween('received_at', [now()->subDays(3), now()->subDay()]);
                    } elseif ($deadline === 'terlambat') {
                        // More than 3 days ago
                        $q->where('received_at', '<', now()->subDays(3));
                    }
                });
            }

            // Eager load relationships
            $query->with([
                'roleData' => function ($q) use ($userRole) {
                    $q->where('role_code', $userRole);
                },
                'dibayarKepadas',
                'bagian'
            ]);

            // Order by most recent first
            $query->orderBy('created_at', 'desc');

            // Paginate results
            $perPage = $request->input('per_page', 20);
            $results = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $results->items(),
                'pagination' => [
                    'total' => $results->total(),
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                ],
                'filters_applied' => $request->only(['search', 'tahun', 'status', 'bagian', 'nilai_min', 'nilai_max', 'deadline'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing search: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available filter options
     */
    public function getFilterOptions(Request $request): JsonResponse
    {
        try {
            $userRole = auth()->user()->role ?? 'team_verifikasi';

            // Get distinct years from documents
            $tahunOptions = Dokumen::selectRaw('DISTINCT YEAR(created_at) as tahun')
                ->whereNotNull('created_at')
                ->orderBy('tahun', 'desc')
                ->pluck('tahun')
                ->toArray();

            // Get available status options for this role
            $statusOptions = [
                'sent_to_team_verifikasi' => 'Inbox / Menunggu Review',
                'sedang diproses' => 'Sedang Diproses',
                'menunggu_di_approve' => 'Menunggu Approval',
                'sent_to_perpajakan' => 'Terkirim ke Perpajakan',
                'sent_to_akutansi' => 'Terkirim ke Akutansi',
                'returned_to_department' => 'Dikembalikan ke Bagian',
            ];

            // Get available bagian
            $bagianOptions = DB::table('bagians')
                ->select('id', 'nama_bagian')
                ->orderBy('nama_bagian')
                ->get()
                ->map(function ($bagian) {
                    return [
                        'id' => $bagian->id,
                        'name' => $bagian->nama_bagian
                    ];
                });

            return response()->json([
                'success' => true,
                'options' => [
                    'tahun' => $tahunOptions,
                    'status' => $statusOptions,
                    'bagian' => $bagianOptions,
                    'deadline' => [
                        'aman' => 'AMAN (< 24 jam)',
                        'peringatan' => 'PERINGATAN (1-3 hari)',
                        'terlambat' => 'TERLAMBAT (> 3 hari)',
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading filter options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load user's saved presets
     */
    public function loadPresets(Request $request): JsonResponse
    {
        try {
            $userRole = auth()->user()->role ?? 'team_verifikasi';

            $presets = SearchPreset::where('user_id', auth()->id())
                ->where('role', $userRole)
                ->orderBy('usage_count', 'desc')
                ->orderBy('last_used_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'presets' => $presets
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading presets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save a new filter preset
     */
    public function savePreset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'filters' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userRole = auth()->user()->role ?? 'team_verifikasi';

            $preset = SearchPreset::create([
                'user_id' => auth()->id(),
                'role' => $userRole,
                'name' => $request->name,
                'filters' => $request->filters,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Filter preset saved successfully',
                'preset' => $preset
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving preset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a filter preset
     */
    public function deletePreset(Request $request, $id): JsonResponse
    {
        try {
            $preset = SearchPreset::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $preset->delete();

            return response()->json([
                'success' => true,
                'message' => 'Preset deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting preset: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update preset usage statistics
     */
    public function usePreset(Request $request, $id): JsonResponse
    {
        try {
            $preset = SearchPreset::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $preset->increment('usage_count');
            $preset->update(['last_used_at' => now()]);

            return response()->json([
                'success' => true,
                'filters' => $preset->filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error using preset: ' . $e->getMessage()
            ], 404);
        }
    }
}
