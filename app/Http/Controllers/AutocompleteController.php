<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DibayarKepada;
use App\Models\Dokumen;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AutocompleteController extends Controller
{
    /**
     * Get suggestions for payment recipients (dibayar kepada)
     */
    public function getPaymentRecipients(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50); // Max 50 suggestions

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = DibayarKepada::where('nama_penerima', 'like', '%' . $query . '%')
            ->select('nama_penerima as name')
            ->distinct()
            ->orderByRaw('CASE WHEN nama_penerima LIKE ? THEN 1 ELSE 2 END', [$query . '%'])
            ->orderBy('nama_penerima')
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        return response()->json($suggestions);
    }

    /**
     * Get suggestions for document senders (nama pengirim)
     */
    public function getDocumentSenders(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Dokumen::where('nama_pengirim', 'like', '%' . $query . '%')
            ->whereNotNull('nama_pengirim')
            ->select('nama_pengirim as name')
            ->distinct()
            ->orderByRaw('CASE WHEN nama_pengirim LIKE ? THEN 1 ELSE 2 END', [$query . '%'])
            ->orderBy('nama_pengirim')
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        return response()->json($suggestions);
    }

    /**
     * Get suggestions for document descriptions (uraian SPP)
     */
    public function getDocumentDescriptions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Dokumen::where('uraian_spp', 'like', '%' . $query . '%')
            ->select('uraian_spp as name')
            ->distinct()
            ->orderByRaw('CASE WHEN uraian_spp LIKE ? THEN 1 ELSE 2 END', [$query . '%'])
            ->orderBy('uraian_spp')
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        return response()->json($suggestions);
    }

    /**
     * Get suggestions for PO numbers
     */
    public function getPONumbers(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = \App\Models\DokumenPO::where('nomor_po', 'like', '%' . $query . '%')
            ->select('nomor_po as name')
            ->distinct()
            ->orderByRaw('CASE WHEN nomor_po LIKE ? THEN 1 ELSE 2 END', [$query . '%'])
            ->orderBy('nomor_po')
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        return response()->json($suggestions);
    }

    /**
     * Get suggestions for PR numbers
     */
    public function getPRNumbers(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = \App\Models\DokumenPR::where('nomor_pr', 'like', '%' . $query . '%')
            ->select('nomor_pr as name')
            ->distinct()
            ->orderByRaw('CASE WHEN nomor_pr LIKE ? THEN 1 ELSE 2 END', [$query . '%'])
            ->orderBy('nomor_pr')
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        return response()->json($suggestions);
    }
}




