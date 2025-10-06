<?php

namespace AmirKateb\AiSuite\Drivers\Ollama;

use Illuminate\Support\Facades\Http;
use AmirKateb\AiSuite\Contracts\DriverInterface;

class OllamaDriver implements DriverInterface
{
    protected array $cfg;

    public function __construct(array $config)
    {
        $this->cfg = $config;
    }

    protected function http()
    {
        $timeout = (int) (config('ai.timeouts.read') ?? 120);
        $connect = (int) (config('ai.timeouts.connect') ?? 10);
        $base = rtrim($this->cfg['base_url'] ?? 'http://localhost:11434', '/');
        return Http::timeout($timeout)->connectTimeout($connect)->baseUrl($base);
    }

    public function listModels(): array
    {
        $res = $this->http()->get('/api/tags');
        if ($res->failed()) {
            throw new \RuntimeException('ollama_list_models_failed');
        }
        return $res->json('models') ?? [];
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? 'llama3';
        $payload = ['model' => $model, 'messages' => $messages, 'stream' => false];
        $res = $this->http()->post('/api/chat', $payload);
        if ($res->failed()) {
            throw new \RuntimeException('ollama_chat_failed');
        }
        return $res->json();
    }

    public function embeddings(string $text, array $options = []): array
    {
        $model = $options['model'] ?? 'llama3';
        $payload = ['model' => $model, 'prompt' => $text];
        $res = $this->http()->post('/api/embeddings', $payload);
        if ($res->failed()) {
            throw new \RuntimeException('ollama_embeddings_failed');
        }
        return $res->json();
    }

    public function ocr(string $imagePath, array $options = []): array
    {
        throw new \RuntimeException('ollama_ocr_unsupported');
    }

    public function image(array $options): array
    {
        throw new \RuntimeException('ollama_image_generation_unsupported');
    }

    public function audioToText(string $filePath, array $options = []): array
    {
        throw new \RuntimeException('ollama_stt_unsupported');
    }

    public function textToAudio(string $text, array $options = []): array
    {
        throw new \RuntimeException('ollama_tts_unsupported');
    }

    public function fineTune(array $options): array
    {
        throw new \RuntimeException('ollama_finetune_unsupported');
    }

    public function calculateCost(array $usage): float
    {
        return 0.0;
    }
}