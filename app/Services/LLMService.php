<?php

namespace App\Services;

use App\LLMs\LLM;
use App\LLMs\Gemini;

class LLMService
{
    protected LLM $client;

    public function __construct()
    {
        $this->client = new Gemini(config('services.gemini.key'));
    }

    public function prompt(string $text): string
    {
        return $this->client->prompt($text);
    }

    public function rephrase(string $text): string
    {
        $prompt = "Rephrase the following text clearly and naturally without changing the meaning:\n\n{$text}";
        return $this->prompt($prompt);
    }
}
