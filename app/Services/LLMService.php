<?php

namespace App\Services;

use function Laravel\Ai\agent;

class LLMService
{
    public function prompt(string $text): string
    {
        return (string) agent()->prompt($text);
    }

    public function rephrase(string $text): string
    {
        $prompt = "Rephrase the following text clearly and naturally in one simple version, without extra explanation:\n\n{$text}";
        return $this->prompt($prompt);
    }

    public function generateDescription(string $title): string
    {
        $prompt = "Write a **short and clear description** for this title. Only return the description text, no extra commentary or formatting:\n\n{$title}";
        return $this->prompt($prompt);
    }

    public function generateTitle(string $description): string
    {
        $prompt = "Write a **short, clear title** for this description. Only return the title text, no extra commentary or formatting:\n\n{$description}";
        return $this->prompt($prompt);
    }
}
