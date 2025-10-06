<?php

namespace AmirKateb\AiSuite\Drivers\OpenAI;

use Illuminate\Support\Facades\Http;
use AmirKateb\AiSuite\Contracts\DriverInterface;

class OpenAIDriver implements DriverInterface
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
        $base = rtrim($this->cfg['base_url'] ?? 'https://api.openai.com/v1', '/');
        $key = $this->cfg['api_key'] ?? '';
        $headers = ['Authorization' => 'Bearer '.$key];
        if (!empty($this->cfg['organization'])) {
            $headers['OpenAI-Organization'] = $this->cfg['organization'];
        }
        if (!empty($this->cfg['project'])) {
            $headers['OpenAI-Project'] = $this->cfg['project'];
        }
        return Http::withHeaders($headers)->timeout($timeout)->connectTimeout($connect)->baseUrl($base);
    }

    public function listModels(): array
    {
        $res = $this->http()->get('/models');
        if ($res->failed()) {
            throw new \RuntimeException('openai_list_models_failed');
        }
        return $res->json('data') ?? [];
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? 'gpt-4o';
        $payload = [
            'model' => $model,
            'messages' => $messages
        ];
        if (isset($options['tools'])) {
            $payload['tools'] = $options['tools'];
        }
        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }
        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = $options['max_tokens'];
        }
        $res = $this->http()->post('/chat/completions', $payload);
        if ($res->failed()) {
            throw new \RuntimeException('openai_chat_failed');
        }
        return $res->json();
    }

    public function embeddings(string $text, array $options = []): array
    {
        $model = $options['model'] ?? 'text-embedding-3-large';
        $payload = ['model' => $model, 'input' => $text];
        $res = $this->http()->post('/embeddings', $payload);
        if ($res->failed()) {
            throw new \RuntimeException('openai_embeddings_failed');
        }
        return $res->json();
    }

    public function ocr(string $imagePath, array $options = []): array
    {
        if (!is_file($imagePath)) {
            throw new \InvalidArgumentException('image_not_found');
        }
        $mime = mime_content_type($imagePath) ?: 'image/png';
        $b64 = base64_encode(file_get_contents($imagePath));
        $dataUrl = 'data:'.$mime.';base64,'.$b64;
        $prompt = $options['prompt'] ?? 'Extract all readable text from this image.';
        $model = $options['model'] ?? 'gpt-4o-mini';
        $messages = [
            ['role' => 'user', 'content' => [
                ['type' => 'text', 'text' => $prompt],
                ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]]
            ]]
        ];
        return $this->chat($messages, ['model' => $model]);
    }

    public function image(array $options): array
    {
        $model = $options['model'] ?? 'gpt-image-1';
        $prompt = $options['prompt'] ?? '';
        $size = $options['size'] ?? '1024x1024';
        $n = $options['n'] ?? 1;
        $payload = ['model' => $model, 'prompt' => $prompt, 'size' => $size, 'n' => $n];
        $res = $this->http()->post('/images/generations', $payload);
        if ($res->failed()) {
            throw new \RuntimeException('openai_image_failed');
        }
        return $res->json();
    }

    public function audioToText(string $filePath, array $options = []): array
    {
        if (!is_file($filePath)) {
            throw new \InvalidArgumentException('audio_not_found');
        }
        $model = $options['model'] ?? 'gpt-4o-transcribe';
        $res = $this->http()->asMultipart()->post('/audio/transcriptions', [
            ['name' => 'model', 'contents' => $model],
            ['name' => 'file', 'contents' => fopen($filePath, 'r'), 'filename' => basename($filePath)]
        ]);
        if ($res->failed()) {
            throw new \RuntimeException('openai_stt_failed');
        }
        return $res->json();
    }

    public function textToAudio(string $text, array $options = []): array
    {
        $model = $options['model'] ?? 'gpt-4o-mini-tts';
        $voice = $options['voice'] ?? 'alloy';
        $format = $options['format'] ?? 'mp3';
        $res = $this->http()->post('/audio/speech', [
            'model' => $model,
            'input' => $text,
            'voice' => $voice,
            'format' => $format
        ]);
        if ($res->failed()) {
            throw new \RuntimeException('openai_tts_failed');
        }
        return $res->json();
    }

    public function fineTune(array $options): array
    {
        $res = $this->http()->post('/fine_tuning/jobs', $options);
        if ($res->failed()) {
            throw new \RuntimeException('openai_finetune_failed');
        }
        return $res->json();
    }

    public function calculateCost(array $usage): float
    {
        $model = $usage['model'] ?? '';
        $in = (float) ($usage['input_tokens'] ?? 0);
        $out = (float) ($usage['output_tokens'] ?? 0);
        $pricing = $this->cfg['pricing'][$model] ?? null;
        if (!$pricing) {
            return 0.0;
        }
        $unit = (float) ($pricing['token_unit'] ?? 1000);
        $inp = (float) ($pricing['input_per_1k'] ?? 0);
        $oup = (float) ($pricing['output_per_1k'] ?? 0);
        $cost = ($in / $unit) * $inp + ($out / $unit) * $oup;
        $round = (int) (config('ai.costing.round') ?? 6);
        return round($cost, $round);
    }
}