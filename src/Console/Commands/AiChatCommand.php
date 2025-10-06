<?php

namespace AmirKateb\AiSuite\Console\Commands;

use Illuminate\Console\Command;
use AmirKateb\AiSuite\AiManager;
use AmirKateb\AiSuite\Support\UsageCalculator;

class AiChatCommand extends Command
{
    protected $signature = 'ai:chat {--driver=} {--model=} {--system=} {--message=*} {--temperature=} {--max_tokens=}';
    protected $description = 'Send a quick chat to the selected AI driver';

    public function handle(): int
    {
        $driver = $this->option('driver') ?: null;
        $model = $this->option('model') ?: null;
        $system = $this->option('system') ?: null;
        $messagesOpt = $this->option('message') ?: [];
        $temperature = $this->option('temperature');
        $maxTokens = $this->option('max_tokens');

        $messages = [];
        if ($system !== null && $system !== '') {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        foreach ($messagesOpt as $m) {
            $messages[] = ['role' => 'user', 'content' => $m];
        }
        if (empty($messages)) {
            $messages[] = ['role' => 'user', 'content' => 'Hello'];
        }

        $options = [];
        if ($model) $options['model'] = $model;
        if ($temperature !== null) $options['temperature'] = (float) $temperature;
        if ($maxTokens !== null) $options['max_tokens'] = (int) $maxTokens;

        $mgr = app(AiManager::class);
        if ($driver) {
            $mgr->driver($driver);
        }
        try {
            $resp = $mgr->chat($messages, $options);
            $provider = $driver ?: config('ai.default');
            $usage = UsageCalculator::parse($provider, $resp, ['model' => $model]);
            $cost = $mgr->calculateCost($usage);
            $this->line(json_encode(['provider' => $provider, 'usage' => $usage, 'cost' => $cost, 'currency' => config('ai.costing.currency'), 'response' => $resp], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}