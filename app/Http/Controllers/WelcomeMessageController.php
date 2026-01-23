<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\WelcomeMessageService;
use App\Models\WelcomeMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

final class WelcomeMessageController extends Controller
{
    public function __construct(
        private WelcomeMessageService $welcomeMessageService
    ) {}

    /**
     * Display welcome messages management page
     */
    public function index(): View
    {
        $messages = WelcomeMessage::orderBy('module')->get();

        return view('admin.welcome-messages.index', compact('messages'));
    }

    /**
     * Get welcome message for specific module (AJAX)
     */
    public function getMessage(Request $request): JsonResponse
    {
        $module = $request->input('module');
        $message = $this->welcomeMessageService->getWelcomeMessage($module);

        return response()->json([
            'message' => $message,
            'module' => $module,
        ]);
    }

    /**
     * Update welcome message
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50',
            'message' => 'required|string|max:255',
            'type' => 'required|in:general,personal,team',
            'is_active' => 'boolean',
        ]);

        try {
            $message = WelcomeMessage::updateOrCreate(
                ['module' => $validated['module'], 'type' => $validated['type']],
                [
                    'message' => $validated['message'],
                    'is_active' => $validated['is_active'] ?? true,
                ]
            );

            // Clear cache for this module
            $this->welcomeMessageService->clearCache($validated['module']);

            return response()->json([
                'success' => true,
                'message' => 'Welcome message updated successfully',
                'data' => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update welcome message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete welcome message
     */
    public function destroy(WelcomeMessage $welcomeMessage): JsonResponse
    {
        try {
            $module = $welcomeMessage->module;
            $welcomeMessage->delete();

            // Clear cache for this module
            $this->welcomeMessageService->clearCache($module);

            return response()->json([
                'success' => true,
                'message' => 'Welcome message deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete welcome message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all welcome message caches
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->welcomeMessageService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'All welcome message caches cleared successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear caches: ' . $e->getMessage(),
            ], 500);
        }
    }
}



