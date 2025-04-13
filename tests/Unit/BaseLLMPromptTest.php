<?php

namespace Prismaticoder\LaravelPromptManager\Tests\Unit;

use Prismaticoder\LaravelPromptManager\Exceptions\VersionNotFound;
use Prismaticoder\LaravelPromptManager\PromptResult;
use Prismaticoder\LaravelPromptManager\Tests\Prompts\BasicTestPrompt;

describe('make method', function() {
    it('should return a new instance of the class', function() {
        $prompt = BasicTestPrompt::make();
        expect($prompt)->toBeInstanceOf(BasicTestPrompt::class);
    });
});

describe('generate method', function() {
    it('should return a PromptResult with the correct version and prompt', function() {
        $prompt = BasicTestPrompt::make();
        $result = $prompt->generate();

        expect($result)->toBeInstanceOf(PromptResult::class);
        expect($result->version)->toBe('v1');
        expect($result->prompt)->toBe('Test prompt v1');
        expect($result->token_count)->toEqual(ceil(strlen($result->prompt) / 4));
        expect($result->name)->toBe('BasicTestPrompt');
    });

    it('should return the prompt for a specific version', function() {
        $prompt = BasicTestPrompt::make();
        $result = $prompt->generate('v2');

        expect($result->version)->toBe('v2');
        expect($result->prompt)->toBe('Test prompt v2');
        expect($result->token_count)->toEqual(ceil(strlen($result->prompt) / 4));
        expect($result->name)->toBe('BasicTestPrompt');
    });

    it('should throw an exception if the version is not found', function() {
        $prompt = BasicTestPrompt::make();
        $prompt->generate('non-existent-version');
    })->throws(VersionNotFound::class, "Version 'non-existent-version' not found for the selected prompt. Available versions are: v1, v2");
});

describe('prompt method', function() {
    it('should return the prompt for the default version', function() {
        $prompt = BasicTestPrompt::make();
        expect($prompt->prompt())->toBe('Test prompt v1');
    });

    it('should return the prompt for a specific version', function() {
        $prompt = BasicTestPrompt::make();
        expect($prompt->prompt('v2'))->toBe('Test prompt v2');
    });

    it('should throw an exception if the version is not found', function() {
        $prompt = BasicTestPrompt::make();
        $prompt->prompt('non-existent-version');
    })->throws(VersionNotFound::class, "Version 'non-existent-version' not found for the selected prompt. Available versions are: v1, v2");
});