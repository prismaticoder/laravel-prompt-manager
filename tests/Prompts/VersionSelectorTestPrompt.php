<?php

namespace Prismaticoder\LaravelPromptManager\Tests\Prompts;

use Closure;
use Prismaticoder\LaravelPromptManager\BaseLLMPrompt;
use Prismaticoder\LaravelPromptManager\Enums\VersionSelector;
use Prismaticoder\LaravelPromptManager\PromptVersionManager;

class VersionSelectorTestPrompt extends BaseLLMPrompt
{
    private VersionSelector|Closure $versionSelectionStrategy;

    public function __construct(VersionSelector|Closure $versionSelectionStrategy = VersionSelector::RANDOM)
    {
        $this->versionSelectionStrategy = $versionSelectionStrategy;
    }

    protected function versionSelector(): VersionSelector|Closure
    {
        return $this->versionSelectionStrategy;
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
