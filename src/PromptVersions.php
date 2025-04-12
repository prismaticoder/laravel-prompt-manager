<?php

namespace Prismaticoder\LaravelPromptManager;

class PromptVersions
{
    public array $versions;

    public function __construct(array $versions)
    {
        if (empty($versions)) {
            throw new \Exception('Versions array cannot be empty');
        }

        foreach ($versions as $version => $generator) {
            if (!is_string($version) || !is_callable($generator)) {
                throw new \Exception('Versions array must be an associative array of version (string) => generator (function)');
            }
        }

        $this->versions = $versions;
    }

    public function getAvailableVersions(): array
    {
        return array_keys($this->versions);
    }

    public function getPrompt(string $version): string
    {
        if (!isset($this->versions[$version])) {
            throw new \Exception("Version {$version} not found");
        }

        $result = call_user_func($this->versions[$version]);
        if (!is_string($result)) {
            throw new \Exception("Generator for version '{$version}' must return a string");
        }

        return $result;
    }
}
