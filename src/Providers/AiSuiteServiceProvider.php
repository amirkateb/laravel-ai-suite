<?php

namespace AmirKateb\AiSuite\Providers;

use Illuminate\Support\ServiceProvider;
use AmirKateb\AiSuite\AiManager;
use AmirKateb\AiSuite\Contracts\HistoryStoreInterface;
use AmirKateb\AiSuite\Support\History\InMemoryHistoryStore;

class AiSuiteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/ai.php' => config_path('ai.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../Config/ai.php', 'ai');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/ai.php', 'ai');

        $this->app->singleton(AiManager::class, function ($app) {
            return new AiManager($app['config']->get('ai', []));
        });

        $this->app->singleton(HistoryStoreInterface::class, function () {
            return new InMemoryHistoryStore();
        });
    }
}