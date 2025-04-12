<?php

namespace Prismaticoder\LaravelPromptManager;

/**
 * Create a new prompt result instance.
 * 
 * @property string $version The version of the prompt
 * @property string $prompt The actual prompt text
 * @property int $token_count The number of tokens in the prompt
 * @property string $name The name of the prompt
 */
class PromptResult
{
    /**
     * Create a new prompt result instance.
     */
    public function __construct(
        public string $version,
        public string $prompt,
        public int $token_count,
        public string $name
    ) {}
} 