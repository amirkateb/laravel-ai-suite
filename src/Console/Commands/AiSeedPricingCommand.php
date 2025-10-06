<?php

namespace AmirKateb\AiSuite\Console\Commands;

use Illuminate\Console\Command;

class AiSeedPricingCommand extends Command
{
    protected $signature = 'ai:seed-pricing';
    protected $description = 'Seed AI model pricing table with default values';

    public function handle(): int
    {
        try {
            $class = 'Database\\Seeders\\AiSuitePricingSeeder';
            if (!class_exists($class)) {
                $class = 'AmirKateb\\AiSuite\\Database\\Seeders\\AiSuitePricingSeeder';
            }
            $this->getLaravel()->call('db:seed', ['--class' => $class, '--force' => true]);
            $this->info('Pricing seeded');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}