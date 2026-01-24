<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteWhatsAppService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected string $countryCode;
    protected int $delay;
    protected bool $enabled;

    public function __construct()
    {
        $this->apiUrl = config('fonnte.api_url', 'https://api.fonnte.com/send');
        $this->apiToken = config('fonnte.api_token', '');
        $this->countryCode = config('fonnte.country_code', '62');
        $this->delay = config('fonnte.delay', 5);
        $this->enabled = config('fonnte.enabled', true);
    }

    /**
     * Send a WhatsApp message to a single recipient
     */
    public function sendMessage(string $phoneNumber, string $message): array
    {
        if (!$this->enabled) {
            Log::info('[Fonnte] WhatsApp notifications disabled. Message not sent.', [
                'phone' => $phoneNumber,
                'message' => substr($message, 0, 100) . '...',
            ]);
            return [
                'success' => false,
                'reason' => 'disabled',
                'message' => 'WhatsApp notifications are disabled',
            ];
        }

        if (empty($this->apiToken)) {
            Log::error('[Fonnte] API token not configured');
            return [
                'success' => false,
                'reason' => 'no_token',
                'message' => 'Fonnte API token not configured',
            ];
        }

        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiToken,
            ])->asForm()->post($this->apiUrl, [
                        'target' => $formattedPhone,
                        'message' => $message,
                        'countryCode' => $this->countryCode,
                    ]);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] === true) {
                Log::info('[Fonnte] Message sent successfully', [
                    'phone' => $formattedPhone,
                    'response' => $result,
                ]);
                return [
                    'success' => true,
                    'response' => $result,
                ];
            }

            Log::warning('[Fonnte] Message failed to send', [
                'phone' => $formattedPhone,
                'response' => $result,
                'http_status' => $response->status(),
            ]);

            return [
                'success' => false,
                'reason' => 'api_error',
                'message' => $result['reason'] ?? 'Unknown error',
                'response' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('[Fonnte] Exception when sending message', [
                'phone' => $formattedPhone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'reason' => 'exception',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send WhatsApp messages to multiple recipients
     */
    public function sendBulkMessages(array $recipients): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            $phone = $recipient['phone'] ?? '';
            $message = $recipient['message'] ?? '';

            if (empty($phone) || empty($message)) {
                $results[] = [
                    'phone' => $phone,
                    'success' => false,
                    'reason' => 'invalid_data',
                ];
                continue;
            }

            $result = $this->sendMessage($phone, $message);
            $results[] = array_merge(['phone' => $phone], $result);

            // Delay between messages to avoid rate limiting
            if ($this->delay > 0 && count($recipients) > 1) {
                sleep($this->delay);
            }
        }

        return $results;
    }

    /**
     * Format phone number to international format
     * Converts 08xxxx to 628xxxx
     */
    public function formatPhoneNumber(string $number): string
    {
        // Remove any non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // If starts with 0, replace with country code
        if (str_starts_with($number, '0')) {
            $number = $this->countryCode . substr($number, 1);
        }

        // If doesn't start with country code, add it
        if (!str_starts_with($number, $this->countryCode)) {
            $number = $this->countryCode . $number;
        }

        return $number;
    }

    /**
     * Check if service is enabled and properly configured
     */
    public function isConfigured(): bool
    {
        return $this->enabled && !empty($this->apiToken);
    }

    /**
     * Test the connection by sending a test message
     */
    public function testConnection(string $phoneNumber): array
    {
        $testMessage = "🔔 *TEST NOTIFIKASI*\n\n" .
            "Ini adalah pesan test dari Sistem Agenda Online PTPN.\n" .
            "Jika Anda menerima pesan ini, berarti integrasi WhatsApp berhasil!\n\n" .
            "⏰ " . now()->format('d/m/Y H:i:s');

        return $this->sendMessage($phoneNumber, $testMessage);
    }
}
