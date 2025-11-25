<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Send WhatsApp notification
     */
    public function sendNotification(string $phoneNumber, string $message): bool
    {
        try {
            // Get WhatsApp API configuration
            $apiUrl = config('whatsapp.api_url');
            $apiKey = config('whatsapp.api_key');
            $apiMethod = config('whatsapp.method', 'fonte'); // fonte, fonnte, or custom
            
            if (!$apiUrl || !$apiKey) {
                Log::warning('WhatsApp API not configured');
                return false;
            }
            
            // Format phone number (remove +, spaces, dashes)
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            
            // Send based on method
            switch ($apiMethod) {
                case 'fonte':
                    return $this->sendViaFonte($phoneNumber, $message, $apiUrl, $apiKey);
                case 'fonnte':
                    return $this->sendViaFonnte($phoneNumber, $message, $apiUrl, $apiKey);
                case 'twilio':
                    return $this->sendViaTwilio($phoneNumber, $message, $apiUrl, $apiKey);
                default:
                    return $this->sendViaCustom($phoneNumber, $message, $apiUrl, $apiKey);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp notification error: ' . $e->getMessage(), [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Send notification to users by role/handler
     */
    public function sendToHandler(string $handler, string $message): array
    {
        $results = [];
        
        // Map handler to role
        $roleMap = [
            'akutansi' => 'Akutansi',
            'perpajakan' => 'Perpajakan',
            'ibu_a' => 'IbuA',
        ];
        
        $role = $roleMap[$handler] ?? null;
        if (!$role) {
            return $results;
        }
        
        // Get users with WhatsApp number for this role
        $users = User::where('role', $role)
            ->whereNotNull('whatsapp_number')
            ->where('whatsapp_number', '!=', '')
            ->get();
        
        foreach ($users as $user) {
            $sent = $this->sendNotification($user->whatsapp_number, $message);
            $results[] = [
                'user' => $user->name,
                'phone' => $user->whatsapp_number,
                'sent' => $sent
            ];
        }
        
        return $results;
    }
    
    /**
     * Format phone number for WhatsApp
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If doesn't start with +, assume Indonesian number and add country code
        if (!str_starts_with($phone, '+')) {
            // Remove leading 0 if exists
            if (str_starts_with($phone, '0')) {
                $phone = substr($phone, 1);
            }
            // Add Indonesia country code
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Send via Fonte API (https://fonte.id)
     */
    private function sendViaFonte(string $phone, string $message, string $apiUrl, string $apiKey): bool
    {
        try {
            $senderNumber = config('whatsapp.sender_number');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'phone' => $phone,
                'message' => $message,
                'sender' => $senderNumber, // Nomor pengirim
            ]);
            
            if ($response->successful()) {
                Log::info('WhatsApp sent via Fonte', ['phone' => $phone]);
                return true;
            }
            
            Log::warning('WhatsApp Fonte API error', [
                'phone' => $phone,
                'response' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Fonte API error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send via Fonnte API (https://fonnte.com)
     */
    private function sendViaFonnte(string $phone, string $message, string $apiUrl, string $apiKey): bool
    {
        try {
            $senderNumber = config('whatsapp.sender_number');
            
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->post($apiUrl, [
                'target' => $phone,
                'message' => $message,
                'sender' => $senderNumber, // Nomor pengirim
            ]);
            
            if ($response->successful()) {
                Log::info('WhatsApp sent via Fonnte', ['phone' => $phone]);
                return true;
            }
            
            Log::warning('WhatsApp Fonnte API error', [
                'phone' => $phone,
                'response' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Fonnte API error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send via Twilio WhatsApp API
     */
    private function sendViaTwilio(string $phone, string $message, string $apiUrl, string $apiKey): bool
    {
        try {
            $accountSid = config('whatsapp.twilio_account_sid');
            $authToken = $apiKey;
            $fromNumber = config('whatsapp.twilio_from_number');
            
            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'From' => "whatsapp:{$fromNumber}",
                    'To' => "whatsapp:{$phone}",
                    'Body' => $message,
                ]);
            
            if ($response->successful()) {
                Log::info('WhatsApp sent via Twilio', ['phone' => $phone]);
                return true;
            }
            
            Log::warning('WhatsApp Twilio API error', [
                'phone' => $phone,
                'response' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Twilio API error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send via Custom API
     */
    private function sendViaCustom(string $phone, string $message, string $apiUrl, string $apiKey): bool
    {
        try {
            $senderNumber = config('whatsapp.sender_number');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'phone' => $phone,
                'message' => $message,
                'sender' => $senderNumber, // Nomor pengirim
            ]);
            
            if ($response->successful()) {
                Log::info('WhatsApp sent via Custom API', ['phone' => $phone]);
                return true;
            }
            
            Log::warning('WhatsApp Custom API error', [
                'phone' => $phone,
                'response' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Custom API error: ' . $e->getMessage());
            return false;
        }
    }
}

