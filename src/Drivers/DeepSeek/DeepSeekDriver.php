<?php

namespace AmirKateb\AiSuite\Drivers\DeepSeek;

use Illuminate\Support\Facades\Http;
use AmirKateb\AiSuite\Contracts\DriverInterface;

class DeepSeekDriver implements DriverInterface
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
        $base = rtrim($this->cfg['base_url'] ?? 'https://api.deepseek.com', '/');
        $key = $this->cfg['api_key'] ?? '';
        return Http::withHeaders(['Authorization' => 'Bearer '.$key])->timeout($timeout)->connectTimeout($connect)->baseUrl($base);
    }

    public function listModels(): array
    {
        $res = $this->http()->get('/v1/models');
        if ($res->failed()) {
            throw new \RuntimeException('deepseek_list_models_failed');
        }
        return $res->json('data') ?? [];
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? 'deepseek-chat';
        $payload = ['model' => $model, 'messages' => $messages];
        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }
        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = $options['max_tokens'];
        }
        $res = $this->http()->post('/v1/chat/completions', $payload);
        if ($res->failed()) {
            throw new \RuntimeException('deepseek_chat_failed');
        }
        return $res->json();
    }

    public function embeddings(string $text, array $options = []): array
    {
        $model = $options['model'] ?? 'deepseek-embedding';
        $payload = ['model' => $model, 'input' => $text];
        $res = $this->http()->post('/v1/embeddings', $payload);
        if ($res->failed()) {
            throw new \RuntimeException('deepseek_embeddings_failed');
        }
        return $res->json();
    }

    public function ocr(string $imagePath, array $options = []): array
    {
        throw new \RuntimeException('deepseek_ocr_unsupported');
    }

    public function image(array $options): array
    {
        throw new \RuntimeException('deepseek_image_generation_unsupported');
    }

    public function audioToText(string $filePath, array $options = []): array
    {
        throw new \RuntimeException('deepseek_stt_unsupported');
    }

    public function textToAudio(string $text, array $options = []): array
    {
        throw new \RuntimeException('deepseek_tts_unsupported');
    }

    public function fineTune(array $options): array
    {
        $res = $this->http()->post('/v1/fine_tuning/jobs', $options);
        if ($res->failed()) {
            throw new \RuntimeException('deepseek_finetune_failed');
        }
        return $res->json();
    }

    public function calculateCost(array $usage): float
    {
        $pricing = $this->cfg['pricing'] ?? [];
        $model = $usage['model'] ?? '';
        $in = (float) ($usage['input_tokens'] ?? 0);
        $out = (float) ($usage['output_tokens'] ?? 0);
        $p = $pricing[$model] ?? null;
        if (!$p) {
            return 0.0;
        }
        $unit = (float) ($p['token_unit'] ?? 1000);
        $inp = (float) ($p['input_per_1k'] ?? 0);
        $oup = (float) ($p['output_per_1k'] ?? 0);
        $cost = ($in / $unit) * $inp + ($out / $unit) * $oup;
        $round = (int) (config('ai.costing.round') ?? 6);
        return round($cost, $round);
    }
}