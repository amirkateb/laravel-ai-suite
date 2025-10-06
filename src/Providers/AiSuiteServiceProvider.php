<?php

namespace AmirKateb\AiSuite\Providers;

use Illuminate\Support\ServiceProvider;

class AiSuiteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/ai.php' => config_path('ai.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../Config/ai.php', 'ai');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/ai.php', 'ai');
    }
}