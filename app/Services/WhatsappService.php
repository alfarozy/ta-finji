<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsappService
{
    public static function sendMessage(string $phone, string $message): array
    {
        $endpoint = rtrim(config('services.whatsapp.host'), '/') . '/message/send-text';
        $session = 'finji';

        try {
            $response = Http::withHeaders([
                'key' => config('services.whatsapp.key'),
            ])->get($endpoint, [
                'session' => $session,
                'to'      => $phone,
                'text'    => $message,
            ]);
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            Log::error('WhatsApp API response failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendMessage failed', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
