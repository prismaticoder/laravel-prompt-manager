<?php

namespace Prismaticoder\LaravelPromptManager;

use Closure;
use Prismaticoder\LaravelPromptManager\Enums\VersionSelector;

abstract class BaseLLMPrompt
{
    private ?VersionManager $versionManager = null;

    /**
     * Create a new prompt instance.
     * 
     * @param mixed ...$args
     * @return static
     */
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
        $this->versionManager = $this->versions();

        $version = $version ?? $this->determineVersion();
        $prompt = $this->versionManager->getPrompt($version);
        $tokenCount = call_user_func($this->tokenCounter(), $prompt);
        $name = $this->getName();
        
        return new PromptResult($version, $prompt, $tokenCount, $name);
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
     * Get a random version.
     */
    private function getRandomVersion(): string
    {
        $versions = $this->getAvailableVersions();
        
        if (empty($versions)) {
            throw new \RuntimeException('No versions available.');
        }
        
        return $versions[array_rand($versions)];
    }

    /**
     * Get all available registered prompt versions.
     */
    protected function getAvailableVersions(): array
    {
        return array_keys($this->versionManager->getVersions());
    }

    /**
     * Define the logic for selecting the version to use for the prompt.
     */
    protected function versionSelector(): VersionSelector|Closure
    {
        return VersionSelector::DEFAULT;
    }

    /**
     * Get the token counting function for prompts.
     * 
     * The default implementation provides a simple approximation based on character count,
     * where 1 token ≈ 4 characters. For more accurate counting, override this method
     * with a model-specific tokenizer.
     * 
     * @see https://platform.openai.com/tokenizer
     * @return Closure Returns a function that accepts a string and returns an integer token count
     */
    protected function tokenCounter(): Closure
    {
        return fn(string $prompt): int => (int) ceil(strlen($prompt) / 4);
    }

    /**
     * Get the name of the prompt.
     */
    protected function getName(): string
    {
        return class_basename($this);
    }

    abstract protected function versions(): VersionManager;

    abstract protected function defaultVersion(): string;
} 