<?php

namespace App\Http\Controllers;

use App\Services\VirtualAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerVirtualAssistantController extends Controller
{
    public function index(): View
    {
        return view('owner.asisten-virtual', [
            'module' => 'owner',
            'menuAsistenVirtual' => 'active',
        ]);
    }

    public function chat(Request $request, VirtualAssistantService $assistant): JsonResponse
    {
        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'min:3',
                'max:' . (int) config('asisten_virtual.limits.max_message_length', 800),
            ],
        ], [
            'message.required' => 'Pertanyaan wajib diisi.',
            'message.min' => 'Pertanyaan terlalu pendek.',
            'message.max' => 'Pertanyaan terlalu panjang. Ringkas pertanyaan agar lebih mudah dianalisis.',
        ]);

        try {
            return response()->json([
                'success' => true,
                'reply' => $assistant->respond($validated['message']),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Maaf, Asisten Virtual belum bisa memproses pertanyaan ini. Coba persempit pertanyaan atau ulangi beberapa saat lagi.',
            ], 500);
        }
    }
}
