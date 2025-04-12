<?php

namespace Prismaticoder\LaravelPromptManager;

class PromptResult
{
    /**
     * Create a new prompt result instance.
     */
    public function __construct(
        public string $version,
        public string $content,
        public int $token_count
    ) {}
} 