<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\Request;

class OpenAITestController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function testBasicCompletion()
    {
        try {
            $prompt = "Analyze this simple sales data and provide a brief summary: 
            Total Sales: $5000
            Number of Orders: 50
            Average Order Value: $100";

            $result = $this->openAIService->generateCompletion($prompt);
            
            return response()->json([
                'status' => 'success',
                'message' => 'OpenAI integration is working!',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'OpenAI integration test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 