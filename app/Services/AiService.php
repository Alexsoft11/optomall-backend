<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected $openaiKey;
    protected $deepseekKey;

    public function __construct()
    {
        $this->openaiKey = env('OPENAI_API_KEY');
        $this->deepseekKey = env('DEEPSEEK_API_KEY');
    }

    public function generateSearchKeys($prompt, $model = 'openai')
    {
        $systemPrompt = "You are a shopping assistant expert in 1688 and Taobao. Your goal is to translate and optimize the user's search query into 3 distinct, high-quality Chinese search keywords that will yield the best product results on 1688 or Taobao. Return ONLY a JSON array of strings, nothing else. Example: [\"keyword1\", \"keyword2\", \"keyword3\"]";

        try {
            if ($model === 'deepseek') {
                return $this->callDeepseek($systemPrompt, $prompt);
            } else {
                return $this->callOpenAi($systemPrompt, $prompt);
            }
        } catch (\Exception $e) {
            Log::channel('ai')->error("AI Service Error ($model): " . $e->getMessage());
            // Fallback: just return the prompt as a single keyword if AI fails
            return [
                'keywords' => [$prompt],
                'usage' => [
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                    'total_tokens' => 0,
                ]
            ];
        }
    }

    protected function callOpenAi($systemPrompt, $userPrompt)
    {
        $response = Http::withToken($this->openaiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 150, // Limit output tokens to prevent waste
        ]);

        if ($response->failed()) {
            throw new \Exception('OpenAI Request Failed: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');
        $usage = $response->json('usage'); // {prompt_tokens, completion_tokens, total_tokens}

        return [
            'keywords' => $this->parseJsonList($content),
            'usage' => [
                'input_tokens' => $usage['prompt_tokens'] ?? 0,
                'output_tokens' => $usage['completion_tokens'] ?? 0,
                'total_tokens' => $usage['total_tokens'] ?? 0,
            ]
        ];
    }

    protected function callDeepseek($systemPrompt, $userPrompt)
    {
        $response = Http::withToken($this->deepseekKey)->post('https://api.deepseek.com/chat/completions', [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 150, // Limit output tokens
        ]);

        if ($response->failed()) {
            throw new \Exception('Deepseek Request Failed: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');
        $usage = $response->json('usage');

        return [
            'keywords' => $this->parseJsonList($content),
            'usage' => [
                'input_tokens' => $usage['prompt_tokens'] ?? 0,
                'output_tokens' => $usage['completion_tokens'] ?? 0,
                'total_tokens' => $usage['total_tokens'] ?? 0,
            ]
        ];
    }

    protected function parseJsonList($content)
    {
        // specific cleaning for JSON array extraction
        $start = strpos($content, '[');
        $end = strrpos($content, ']');
        if ($start !== false && $end !== false) {
            $json = substr($content, $start, $end - $start + 1);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        // Fallback if parsing fails but content exists
        return [$content];
    }
}
