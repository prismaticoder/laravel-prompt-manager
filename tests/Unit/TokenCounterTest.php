<?php

namespace Prismaticoder\LaravelPromptManager\Tests\Unit\Prompts;

use Prismaticoder\LaravelPromptManager\Tests\Prompts\TokenCounterTestPrompt;

describe('Token Counter Behavior', function() {
    it('uses the default token counter', function() {
        $prompt = TokenCounterTestPrompt::make();

        $result = $prompt->generate();
        expect($result->token_count)->toEqual(ceil(strlen($result->prompt) / 4));
    });

    it('uses a custom token counter', function() {
        $prompt = TokenCounterTestPrompt::make(fn($prompt) => strlen($prompt) / 2);

        $result = $prompt->generate();
        expect($result->token_count)->toEqual(ceil(strlen($result->prompt) / 2));
    });
}); 