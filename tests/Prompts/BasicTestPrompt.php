<?php

namespace Prismaticoder\LaravelPromptManager\Tests\Prompts;

use Prismaticoder\LaravelPromptManager\BaseLLMPrompt;
use Prismaticoder\LaravelPromptManager\PromptVersionManager;

class BasicTestPrompt extends BaseLLMPrompt
{
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
