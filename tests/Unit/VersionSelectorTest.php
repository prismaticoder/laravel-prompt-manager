<?php

namespace Prismaticoder\LaravelPromptManager\Tests\Unit\Prompts;

use Prismaticoder\LaravelPromptManager\Enums\VersionSelector;
use Prismaticoder\LaravelPromptManager\Tests\Prompts\VersionSelectorTestPrompt;

describe('Version Selector Behavior', function() {
    it('uses the default version when specified', function() {
        $prompt = VersionSelectorTestPrompt::make(VersionSelector::DEFAULT);

        $result = $prompt->generate();
        expect($result->version)->toBe('v1');
    });

    it('uses a random version when specified', function() {
        $prompt = VersionSelectorTestPrompt::make(VersionSelector::RANDOM);

        $chosenVersions = [];

        for ($i = 0; $i < 15; $i++) {
            $result = $prompt->generate();
            $chosenVersions[] = $result->version;
        }

        expect($chosenVersions)->not->toBeEmpty();
        expect($chosenVersions)->toContain('v1', 'v2');
    });

    it('uses a version based on the result of a closure', function() {
        $prompt = VersionSelectorTestPrompt::make(fn() => 'v2');

        $result = $prompt->generate();
        expect($result->version)->toBe('v2');
    });
}); 