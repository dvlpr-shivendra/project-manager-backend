<?php

use App\Services\LLMService;
use Laravel\Ai\Ai;
use Laravel\Ai\AnonymousAgent;

test('it can prompt text', function () {
    Ai::fakeAgent(AnonymousAgent::class, [
        'Mocked response',
    ]);

    $service = new LLMService();
    $result = $service->prompt('Hello');

    expect($result)->toBe('Mocked response');
});

test('it can rephrase text', function () {
    Ai::fakeAgent(AnonymousAgent::class, [
        'Rephrased version',
    ]);

    $service = new LLMService();
    $result = $service->rephrase('Some text to rephrase');

    expect($result)->toBe('Rephrased version');
});

test('it can generate a description', function () {
    Ai::fakeAgent(AnonymousAgent::class, [
        'This is a description',
    ]);

    $service = new LLMService();
    $result = $service->generateDescription('A title');

    expect($result)->toBe('This is a description');
});

test('it can generate a title', function () {
    Ai::fakeAgent(AnonymousAgent::class, [
        'A New Title',
    ]);

    $service = new LLMService();
    $result = $service->generateTitle('A description of something');

    expect($result)->toBe('A New Title');
});
