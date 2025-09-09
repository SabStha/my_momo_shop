<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    /**
     * Send push notifications to multiple devices.
     */
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        if (empty($tokens)) {
            return;
        }

        $client = new Client(['base_uri' => 'https://exp.host/--/api/v2/']);
        
        // Process tokens in chunks of 100 (Expo's limit)
        $chunks = array_chunk($tokens, 100);
        
        foreach ($chunks as $chunk) {
            $messages = array_map(function ($token) use ($title, $body, $data) {
                return [
                    'to' => $token,
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                    'sound' => 'default',
                    'channelId' => 'default',
                ];
            }, $chunk);

            try {
                $response = $client->post('push/send', [
                    'json' => $messages,
                    'timeout' => 10,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    Log::info('Expo push notifications sent successfully', [
                        'count' => count($chunk),
                        'tokens' => $chunk
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Expo push failed: ' . $e->getMessage(), [
                    'count' => count($chunk),
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send a single push notification.
     */
    public function sendSingle(string $token, string $title, string $body, array $data = []): void
    {
        $this->send([$token], $title, $body, $data);
    }
}
