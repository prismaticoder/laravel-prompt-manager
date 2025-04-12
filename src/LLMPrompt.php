<?php

namespace Prismaticoder\LaravelPromptManager;

use Closure;
use Prismaticoder\LaravelPromptManager\Enums\VersionSelector;

abstract class LLMPrompt
{
    public static function make(mixed ...$args): self
    {
        return new static(...$args);
    }

    /**
     * Generate the prompt using the selected version method.
     * @param string|null $version
     * @return PromptResult
     */
    public function generate(?string $version = null): PromptResult
    {
        $version = $version ?? $this->determineVersion();
        $prompt = $this->versions()->getPrompt($version);
        $tokenCount = call_user_func($this->tokenCounter($prompt));
        
        return new PromptResult($version, $prompt, $tokenCount);
    }

    /**
     * Get the prompt for the selected version.
     * @param string|null $version
     * @return string
     */
    public function prompt(?string $version = null): string
    {
        return $this->generate($version)->prompt;
    }

    /**
     * Determine which version to use based on the selection strategy.
     */
    protected function determineVersion(): string
    {
        $versionSelector = $this->versionSelector();

        if ($versionSelector instanceof Closure) {
            return call_user_func($versionSelector, $this->getAvailableVersions());
        }

        return match ($versionSelector) {
            VersionSelector::DEFAULT => $this->defaultVersion(),
            VersionSelector::RANDOM => $this->getRandomVersion(),
            default => $this->defaultVersion(),
        };
    }

    /**
     * Get all available registered prompt versions.
     */
    protected function getAvailableVersions(): array
    {
        return $this->versions()->getAvailableVersions();
    }

    /**
     * Get a random version.
     */
    protected function getRandomVersion(): string
    {
        $versions = $this->getAvailableVersions();
        
        if (empty($versions)) {
            throw new \RuntimeException('No versions available.');
        }
        
        return $versions[array_rand($versions)];
    }

    /**
     * Define the logic for selecting the version to use for the prompt.
     */
    protected function versionSelector(): VersionSelector|Closure
    {
        return VersionSelector::DEFAULT;
    }

    /**
     * Count tokens in a prompt (can be overridden with a more accurate implementation).
     */
    protected function tokenCounter(string $prompt): Closure
    {
        return fn(string $prompt): int => (int) ceil(strlen($prompt) / 3.7);
    }

    abstract protected function versions(): PromptVersions;

    abstract protected function defaultVersion(): string;
} 