<?php

namespace App\LLMs;

use Illuminate\Support\Facades\Http;

class Gemini implements LLM
{
    protected string $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent';

    public function __construct(protected string $apiKey) {}

    public function prompt(string $text): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-goog-api-key' => $this->apiKey,
        ])->post($this->url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $text],
                    ],
                ],
            ],
        ]);

        return $response->json('candidates.0.content.parts.0.text', '');
    }
}
