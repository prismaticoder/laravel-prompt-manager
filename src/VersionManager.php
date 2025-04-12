<?php

namespace Prismaticoder\LaravelPromptManager;

use InvalidArgumentException;
use Prismaticoder\LaravelPromptManager\Exceptions\VersionNotFound;
use RuntimeException;

class VersionManager
{
    public array $versions;

    public function __construct(array $versions)
    {
        if (empty($versions)) {
            throw new \Exception('Versions array cannot be empty');
        }

        foreach ($versions as $version => $generator) {
            if (! is_string($version)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid version key passed: expected string, got %s',
                    get_debug_type($version)
                ));
            }

            if (! is_callable($generator)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid version generator passed: expected callable, got %s',
                    get_debug_type($generator)
                ));
            }
        }

        $this->versions = $versions;
    }

    public function getPrompt(string $version): string
    {
        if (! isset($this->versions[$version])) {
            throw new VersionNotFound($version, array_keys($this->versions));
        }

        $prompt = call_user_func($this->versions[$version]);

        if (! is_string($prompt)) {
            throw new RuntimeException(sprintf(
                "Generator for version '%s' must return a string, got %s",
                $version,
                get_debug_type($prompt)
            ));
        }

        return $prompt;
    }
}
