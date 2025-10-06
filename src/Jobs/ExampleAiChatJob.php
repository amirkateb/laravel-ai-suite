<?php

namespace AmirKateb\AiSuite\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use AmirKateb\AiSuite\AiManager;

class ExampleAiChatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $prompt;
    protected ?string $model;
    protected ?string $driver;

    public function __construct(string $prompt, ?string $model = null, ?string $driver = null)
    {
        $this->prompt = $prompt;
        $this->model = $model;
        $this->driver = $driver;
        $this->onQueue('ai_example');
    }

    public function handle(AiManager $ai): void
    {
        if ($this->driver) {
            $ai->driver($this->driver);
        }

        $ai->chat([
            ['role' => 'system', 'content' => 'You are a background AI processor.'],
            ['role' => 'user', 'content' => $this->prompt]
        ], [
            'model' => $this->model,
            'conversation_id' => 'example-job-' . uniqid(),
        ]);
    }
}