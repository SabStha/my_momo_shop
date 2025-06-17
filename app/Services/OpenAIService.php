<?php

namespace App\Services;

use OpenAI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $client;
    protected $cacheTime = 3600; // 1 hour cache

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
    }

    /**
     * Generate a completion using OpenAI
     *
     * @param string $prompt
     * @param array $options
     * @return string
     */
    public function generateCompletion(string $prompt, array $options = [])
    {
        try {
            $cacheKey = 'openai_' . md5($prompt . json_encode($options));
            
            return Cache::remember($cacheKey, $this->cacheTime, function () use ($prompt, $options) {
                $response = $this->client->chat()->create([
                    'model' => $options['model'] ?? 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => $options['temperature'] ?? 0.7,
                    'max_tokens' => $options['max_tokens'] ?? 1000,
                ]);

                return $response->choices[0]->message->content;
            });
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate sales analysis
     *
     * @param array $salesData
     * @return string
     */
    public function generateSalesAnalysis(array $salesData)
    {
        $prompt = "Analyze the following sales data and provide insights:\n" . json_encode($salesData, JSON_PRETTY_PRINT);
        return $this->generateCompletion($prompt);
    }

    /**
     * Generate product performance analysis
     *
     * @param array $productData
     * @return string
     */
    public function generateProductAnalysis(array $productData)
    {
        $prompt = "Analyze the following product performance data and provide insights:\n" . json_encode($productData, JSON_PRETTY_PRINT);
        return $this->generateCompletion($prompt);
    }
} 