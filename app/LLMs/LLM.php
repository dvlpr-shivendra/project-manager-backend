<?php

namespace App\LLMs;

interface LLM {
    public function prompt(string $text): string;
}