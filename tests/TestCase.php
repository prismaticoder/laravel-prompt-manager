<?php

namespace Prismaticoder\LaravelPromptManager\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Prismaticoder\LaravelPromptManager\PromptManagerServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PromptManagerServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
} 