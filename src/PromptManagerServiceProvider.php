<?php

namespace Prismaticoder\LaravelPromptManager;

use Illuminate\Support\ServiceProvider;
use Prismaticoder\LaravelPromptManager\Console\Commands\MakePromptCommand;

class PromptManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakePromptCommand::class,
            ]);
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register any needed services here
    }
} 