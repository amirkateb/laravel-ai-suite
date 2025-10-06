<?php

namespace AmirKateb\AiSuite\Providers;

use Illuminate\Support\ServiceProvider;
use AmirKateb\AiSuite\AiManager;
use AmirKateb\AiSuite\Contracts\HistoryStoreInterface;
use AmirKateb\AiSuite\Support\History\InMemoryHistoryStore;
use AmirKateb\AiSuite\Console\Commands\AiModelsCommand;
use AmirKateb\AiSuite\Console\Commands\AiChatCommand;
use AmirKateb\AiSuite\Http\Middleware\SetAiDriverFromHeader;
use AmirKateb\AiSuite\Contracts\FineTuneStoreInterface;
use AmirKateb\AiSuite\Support\FineTune\FileFineTuneStore;

class AiSuiteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/ai.php' => config_path('ai.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../Config/ai.php', 'ai');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/fine_tune.php');

        if (file_exists(__DIR__ . '/../Support/helpers.php')) {
            require_once __DIR__ . '/../Support/helpers.php';
        }

        $router = $this->app['router'];
        $router->aliasMiddleware('ai.driver', SetAiDriverFromHeader::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                AiModelsCommand::class,
                AiChatCommand::class,
            ]);
        }
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

        $this->app->singleton(FineTuneStoreInterface::class, function () {
            return new FileFineTuneStore();
        });
    }
}