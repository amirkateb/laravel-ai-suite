<?php

namespace AmirKateb\AiSuite\Drivers\Anthropic;

use Illuminate\Support\Facades\Http;
use AmirKateb\AiSuite\Contracts\DriverInterface;

class AnthropicDriver implements DriverInterface
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
        $base = rtrim($this->cfg['base_url'] ?? 'https://api.anthropic.com', '/');
        $key = $this->cfg['api_key'] ?? '';
        $version = $this->cfg['version'] ?? '2023-06-01';
        return Http::withHeaders([
            'x-api-key' => $key,
            'anthropic-version' => $version,
            'content-type' => 'application/json'
        ])->timeout($timeout)->connectTimeout($connect)->baseUrl($base);
    }

    public function listModels(): array
    {
        $res = $this->http()->get('/v1/models');
        if ($res->failed()) {
            throw new \RuntimeException('anthropic_list_models_failed');
        }
        return $res->json('data') ?? [];
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? 'claude-3-5-sonnet-20240620';
        $input = '';
        foreach ($messages as $m) {
            $input .= strtoupper($m['role']).": ".$m['content']."\n";
        }
        $payload = ['model' => $model, 'max_tokens' => $options['max_tokens'] ?? 1024, 'messages' => $messages];
        $res = $this->http()->post('/v1/messages', $payload);
        if ($res->failed()) {
            throw new \RuntimeException('anthropic_chat_failed');
        }
        return $res->json();
    }

    public function embeddings(string $text, array $options = []): array
    {
        throw new \RuntimeException('anthropic_embeddings_unsupported');
    }

    public function ocr(string $imagePath, array $options = []): array
    {
        throw new \RuntimeException('anthropic_ocr_unsupported');
    }

    public function image(array $options): array
    {
        throw new \RuntimeException('anthropic_image_generation_unsupported');
    }

    public function audioToText(string $filePath, array $options = []): array
    {
        throw new \RuntimeException('anthropic_stt_unsupported');
    }

    public function textToAudio(string $text, array $options = []): array
    {
        throw new \RuntimeException('anthropic_tts_unsupported');
    }

    public function fineTune(array $options): array
    {
        throw new \RuntimeException('anthropic_finetune_unsupported');
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