<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsappService
{
    public static function sendMessage(string $phone, string $message): array
    {
        $endpoint = trim(config('services.whatsapp.host'), '/') . '/api/whatsapp/messages/text';

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'X-API-Key' => config('services.whatsapp.key'),
                    'Accept'    => 'application/json',
                ])
                ->post($endpoint, [
                    'to'          => $phone,
                    'text'        => $message,
                    'preview_url' => false,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            Log::error('WhatsApp API response failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'error'  => $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Throwable $e) {

            Log::error('WhatsApp sendMessage failed', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
