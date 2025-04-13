<?php

use Prismaticoder\LaravelPromptManager\Exceptions\VersionNotFound;
use Prismaticoder\LaravelPromptManager\PromptVersionManager;

beforeEach(function() {
    $this->versions = [
        'v1' => fn() => 'Test prompt v1',
        'v2' => fn() => 'Test prompt v2',
    ];
});

describe('constructor', function() {
    it('should throw an exception if no versions are provided', function() {
        new PromptVersionManager([]);
    })->throws(InvalidArgumentException::class, 'Versions array cannot be empty');

    it('should throw an exception if a version key is not a string', function() {
        new PromptVersionManager([
            1 => fn() => 'Test prompt v1',
        ]);
    })->throws(InvalidArgumentException::class, 'Invalid version key passed: expected string, got int');

    it('should throw an exception if a version generator is not callable', function() {
        new PromptVersionManager([
            'v1' => 'not a callable',
        ]);
    })->throws(InvalidArgumentException::class, 'Invalid version generator passed: expected callable, got string');

    it('should create a valid version manager if all versions are valid', function() {
        $manager = new PromptVersionManager($this->versions);

        expect($manager)->toBeInstanceOf(PromptVersionManager::class);
    });
});

describe('getVersions', function() {
    it('should return an array of versions', function() {
        $manager = new PromptVersionManager($this->versions);

        expect($manager->getVersions())->toBe($this->versions);
    });
});

describe('getPrompt', function() {
    it('should return the prompt for a given version', function() {
        $manager = new PromptVersionManager($this->versions);

        expect($manager->getPrompt('v1'))->toBe(call_user_func($this->versions['v1']));
        expect($manager->getPrompt('v2'))->toBe(call_user_func($this->versions['v2']));
    });

    it('should throw an exception if the version is non-existent', function() {
        $manager = new PromptVersionManager($this->versions);

        $manager->getPrompt('non-existent-version');
    })->throws(VersionNotFound::class, "Version 'non-existent-version' not found for the selected prompt. Available versions are: v1, v2");

    it('should throw an exception if the version generator does not return a string', function() {
        $manager = new PromptVersionManager([
            'v1' => fn() => ['not a string'],
        ]);

        $manager->getPrompt('v1');
    })->throws(RuntimeException::class, 'Generator for version \'v1\' must return a string, got array');
});