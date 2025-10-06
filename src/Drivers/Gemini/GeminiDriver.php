<?php

namespace AmirKateb\AiSuite\Drivers\Gemini;

use Illuminate\Support\Facades\Http;
use AmirKateb\AiSuite\Contracts\DriverInterface;

class GeminiDriver implements DriverInterface
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
        $base = rtrim($this->cfg['base_url'] ?? 'https://generativelanguage.googleapis.com', '/');
        $key = $this->cfg['api_key'] ?? '';
        return Http::timeout($timeout)->connectTimeout($connect)->baseUrl($base)->withQueryParameters(['key' => $key]);
    }

    public function listModels(): array
    {
        $res = $this->http()->get('/v1beta/models');
        if ($res->failed()) {
            throw new \RuntimeException('gemini_list_models_failed');
        }
        return $res->json('models') ?? [];
    }

    protected function mapMessages(array $messages): array
    {
        $contents = [];
        foreach ($messages as $m) {
            $role = $m['role'] === 'system' ? 'user' : ($m['role'] ?? 'user');
            $parts = [];
            if (is_array($m['content'])) {
                foreach ($m['content'] as $c) {
                    if (is_array($c) && isset($c['type']) && $c['type'] === 'text') {
                        $parts[] = ['text' => $c['text']];
                    } elseif (is_array($c) && isset($c['type']) && $c['type'] === 'image_url') {
                        if (is_array($c['image_url']) && isset($c['image_url']['url'])) {
                            $url = $c['image_url']['url'];
                            if (str_starts_with($url, 'data:')) {
                                $data = explode(',', $url, 2)[1] ?? '';
                                $mime = 'image/png';
                                $parts[] = ['inline_data' => ['mime_type' => $mime, 'data' => $data]];
                            } else {
                                $parts[] = ['file_data' => ['file_uri' => $url, 'mime_type' => 'image/*']];
                            }
                        }
                    }
                }
            } else {
                $parts[] = ['text' => (string) $m['content']];
            }
            $contents[] = ['role' => $role, 'parts' => $parts];
        }
        return $contents;
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? 'gemini-1.5-flash';
        $payload = ['contents' => $this->mapMessages($messages)];
        if (isset($options['temperature'])) {
            $payload['generationConfig']['temperature'] = $options['temperature'];
        }
        if (isset($options['max_tokens'])) {
            $payload['generationConfig']['maxOutputTokens'] = $options['max_tokens'];
        }
        $res = $this->http()->post('/v1beta/models/'.$model.':generateContent', $payload);
        if ($res->failed()) {
            throw new \RuntimeException('gemini_chat_failed');
        }
        return $res->json();
    }

    public function embeddings(string $text, array $options = []): array
    {
        $model = $options['model'] ?? 'text-embedding-004';
        $payload = ['model' => $model, 'content' => ['parts' => [['text' => $text]]]];
        $res = $this->http()->post('/v1beta/models/'.$model.':embedContent', $payload);
        if ($res->failed()) {
            throw new \RuntimeException('gemini_embeddings_failed');
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
        $prompt = $options['prompt'] ?? 'Extract all readable text from this image.';
        $model = $options['model'] ?? 'gemini-1.5-flash';
        $messages = [
            ['role' => 'user', 'content' => [
                ['type' => 'text', 'text' => $prompt],
                ['type' => 'image_url', 'image_url' => ['url' => 'data:'.$mime.';base64,'.$b64]]
            ]]
        ];
        return $this->chat($messages, ['model' => $model]);
    }

    public function image(array $options): array
    {
        throw new \RuntimeException('gemini_image_generation_unsupported');
    }

    public function audioToText(string $filePath, array $options = []): array
    {
        throw new \RuntimeException('gemini_stt_unsupported');
    }

    public function textToAudio(string $text, array $options = []): array
    {
        throw new \RuntimeException('gemini_tts_unsupported');
    }

    public function fineTune(array $options): array
    {
        throw new \RuntimeException('gemini_finetune_unsupported');
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