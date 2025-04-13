<?php

namespace Prismaticoder\LaravelPromptManager\Tests\Prompts;

use Closure;
use Prismaticoder\LaravelPromptManager\BaseLLMPrompt;
use Prismaticoder\LaravelPromptManager\PromptVersionManager;

class TokenCounterTestPrompt extends BaseLLMPrompt
{
    private ?Closure $tokenCounterStrategy;

    public function __construct(?Closure $tokenCounterStrategy = null)
    {
        $this->tokenCounterStrategy = $tokenCounterStrategy ?? fn($prompt) => ceil(strlen($prompt) / 4);
    }

    protected function tokenCounter(): Closure
    {
        return $this->tokenCounterStrategy;
    }

    protected function versions(): PromptVersionManager
    {
        return new PromptVersionManager([
            'v1' => fn() => 'Test prompt v1',
            'v2' => fn() => 'Test prompt v2',
        ]);
    }

    protected function defaultVersion(): string
    {
        return 'v1';
    }
}
