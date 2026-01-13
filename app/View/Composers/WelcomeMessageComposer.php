<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Services\WelcomeMessageService;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

final readonly class WelcomeMessageComposer
{
    public function __construct(
        private WelcomeMessageService $welcomeMessageService
    ) {
    }

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        try {
            $module = $view->getData()['module'] ?? null;

            if (!$module && request()->route()) {
                // Try to get module from route name or controller
                $module = $this->extractModuleFromRoute();
            }

            $welcomeMessage = $this->welcomeMessageService->getWelcomeMessage($module);

            $view->with('welcomeMessage', $welcomeMessage);
        } catch (\Exception $e) {
            Log::error('Error in WelcomeMessageComposer', [
                'view' => $view->name(),
                'error' => $e->getMessage()
            ]);

            // Provide fallback message
            $view->with('welcomeMessage', 'Selamat datang di Agenda Online PTPN');
        }
    }

    /**
     * Extract module from current route
     */
    private function extractModuleFromRoute(): ?string
    {
        $routeName = request()->route()->getName();

        if (!$routeName) {
            return null;
        }

        // Map route patterns to modules
        return match (true) {
            str_contains($routeName, 'dokumens') && !str_contains($routeName, 'dokumensPembayaran') &&
            !str_contains($routeName, 'dokumensAkutansi') && !str_contains($routeName, 'dokumensPerpajakan') => 'IbuA',

            str_contains($routeName, 'dokumensB') => 'ibuB',
            str_contains($routeName, 'dokumensPerpajakan') => 'perpajakan',
            str_contains($routeName, 'dokumensAkutansi') => 'akutansi',
            str_contains($routeName, 'dokumensPembayaran') => 'pembayaran',

            str_contains($routeName, 'dashboard') => $this->getModuleFromDashboardRoute($routeName),

            // Detect owner routes (/owner/home, /owner/dokumen, /owner/rekapan-keterlambatan)
            str_contains($routeName, 'owner.') => 'owner',
            str_starts_with(request()->path(), 'owner') => 'owner',

            default => null,
        };
    }

    /**
     * Get module from dashboard routes
     */
    private function getModuleFromDashboardRoute(string $routeName): ?string
    {
        return match ($routeName) {
            'dashboard' => 'IbuA',
            'dashboardB' => 'ibuB',
            'dashboardPerpajakan' => 'perpajakan',
            'dashboardAkutansi' => 'akutansi',
            'dashboardPembayaran' => 'pembayaran',
            'owner.dashboard' => 'owner',
            default => null,
        };
    }
}