<?php

namespace Prismaticoder\LaravelPromptManager;

class PromptResult
{
    /**
     * Create a new prompt result instance.
     */
    public function __construct(
        public string $version,
        public string $prompt,
        public int $token_count
    ) {}
} 