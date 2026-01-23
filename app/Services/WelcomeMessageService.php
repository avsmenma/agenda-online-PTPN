<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WelcomeMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

final readonly class WelcomeMessageService
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get welcome message for specific module
     */
    public function getWelcomeMessage(?string $module = null): string
    {
        try {
            $cacheKey = "welcome_message_{$module}";

            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($module) {
                $welcomeMessage = $this->fetchWelcomeMessage($module);

                if (!$welcomeMessage) {
                    $message = $this->getDefaultMessage($module);
                } else {
                    $message = $welcomeMessage->message;
                }

                return $this->formatMessage($message, $module);
            });
        } catch (\Exception $e) {
            Log::error('Error getting welcome message', [
                'module' => $module,
                'error' => $e->getMessage()
            ]);

            return $this->getFallbackMessage($module);
        }
    }

    /**
     * Fetch welcome message from database
     */
    private function fetchWelcomeMessage(?string $module): ?WelcomeMessage
    {
        if (!$module) {
            return null;
        }

        return WelcomeMessage::active()
            ->byModule($module)
            ->first();
    }

    /**
     * Get default message based on module
     */
    private function getDefaultMessage(?string $module): string
    {
        return match ($module) {
            'Operator' => 'Selamat datang, Ibu Tarapul',
            'Team Verifikasi' => 'Selamat datang, Team Verifikasi',
            'perpajakan' => 'Selamat datang, Team Perpajakan',
            'akutansi' => 'Selamat datang, Team Akutansi',
            'pembayaran' => '{greeting}, Team Pembayaran!',
            'owner' => '',
            default => 'Selamat datang di Agenda Online PTPN',
        };
    }

    /**
     * Format message with dynamic elements
     */
    private function formatMessage(string $message, ?string $module): string
    {
        $greeting = $this->getGreeting();

        $formatted = str_replace(
            ['{greeting}', '{module}', '{time}'],
            [$greeting, ucfirst($module ?? 'Dashboard'), now()->format('H:i')],
            $message
        );

        return $formatted;
    }

    /**
     * Get greeting based on time of day
     */
    private function getGreeting(): string
    {
        $hour = (int) now()->format('H');

        return match (true) {
            $hour < 10 => 'Selamat Pagi',
            $hour < 15 => 'Selamat Siang',
            $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };
    }

    /**
     * Get fallback message in case of errors
     */
    private function getFallbackMessage(?string $module): string
    {
        return 'Selamat datang di Agenda Online PTPN';
    }

    /**
     * Clear cache for specific module or all
     */
    public function clearCache(?string $module = null): void
    {
        if ($module) {
            Cache::forget("welcome_message_{$module}");
        } else {
            // Clear all welcome message caches
            $modules = ['Operator', 'Team Verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];

            foreach ($modules as $mod) {
                Cache::forget("welcome_message_{$mod}");
            }
        }
    }

    /**
     * Get all available modules with their messages
     */
    public function getAllMessages(): array
    {
        return WelcomeMessage::active()
            ->get()
            ->keyBy('module')
            ->toArray();
    }
}


