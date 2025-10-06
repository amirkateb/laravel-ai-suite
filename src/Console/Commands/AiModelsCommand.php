<?php

namespace AmirKateb\AiSuite\Console\Commands;

use Illuminate\Console\Command;
use AmirKateb\AiSuite\AiManager;

class AiModelsCommand extends Command
{
    protected $signature = 'ai:models {driver?}';
    protected $description = 'List models of a driver (or default driver)';

    public function handle(): int
    {
        $driver = $this->argument('driver');
        $mgr = app(AiManager::class);
        try {
            $models = $mgr->listModels($driver);
            $this->line(json_encode($models, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}