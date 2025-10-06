<?php

namespace AmirKateb\AiSuite\Drivers\AzureOpenAI;

use Illuminate\Support\Facades\Http;
use AmirKateb\AiSuite\Contracts\DriverInterface;

class AzureOpenAIDriver implements DriverInterface
{
    protected array $cfg;

    public function __construct(array $config)
    {
        $this->cfg = $config;
    }

    protected function base(): string
    {
        return rtrim((string)($this->cfg['endpoint'] ?? ''), '/');
    }

    protected function version(): string
    {
        return (string)($this->cfg['api_version'] ?? '2024-06-01');
    }

    protected function key(): string
    {
        return (string)($this->cfg['api_key'] ?? '');
    }

    protected function http()
    {
        $timeout = (int)(config('ai.timeouts.read') ?? 120);
        $connect = (int)(config('ai.timeouts.connect') ?? 10);
        return Http::withHeaders(['api-key' => $this->key()])
            ->timeout($timeout)
            ->connectTimeout($connect)
            ->baseUrl($this->base());
    }

    public function listModels(): array
    {
        $res = $this->http()->get('/openai/deployments', ['api-version' => $this->version()]);
        if ($res->failed()) {
            throw new \RuntimeException('azure_openai_list_models_failed');
        }
        return $res->json('data') ?? ($res->json('value') ?? []);
    }

    public function chat(array $messages, array $options = []): array
    {
        $deployment = $options['deployment'] ?? ($this->cfg['deployment'] ?? '');
        if ($deployment === '') {
            throw new \InvalidArgumentException('deployment_required');
        }
        $payload = ['messages' => $messages];
        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }
        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = $options['max_tokens'];
        }
        if (isset($options['tools'])) {
            $payload['tools'] = $options['tools'];
        }
        $res = $this->http()->post('/openai/deployments/'.$deployment.'/chat/completions', array_merge($payload, ['api-version' => $this->version()]));
        if ($res->failed()) {
            throw new \RuntimeException('azure_openai_chat_failed');
        }
        return $res->json();
    }

    public function embeddings(string $text, array $options = []): array
    {
        $deployment = $options['deployment'] ?? ($this->cfg['deployment_embeddings'] ?? '');
        if ($deployment === '') {
            throw new \InvalidArgumentException('deployment_embeddings_required');
        }
        $payload = ['input' => $text];
        $res = $this->http()->post('/openai/deployments/'.$deployment.'/embeddings', array_merge($payload, ['api-version' => $this->version()]));
        if ($res->failed()) {
            throw new \RuntimeException('azure_openai_embeddings_failed');
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
        $deployment = $options['deployment'] ?? ($this->cfg['deployment'] ?? '');
        if ($deployment === '') {
            throw new \InvalidArgumentException('deployment_required');
        }
        $messages = [
            ['role' => 'user', 'content' => [
                ['type' => 'text', 'text' => $prompt],
                ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]]
            ]]
        ];
        return $this->chat($messages, ['deployment' => $deployment, 'temperature' => $options['temperature'] ?? null, 'max_tokens' => $options['max_tokens'] ?? null]);
    }

    public function image(array $options): array
    {
        throw new \RuntimeException('azure_openai_image_generation_requires_image_deployment');
    }

    public function audioToText(string $filePath, array $options = []): array
    {
        throw new \RuntimeException('azure_openai_stt_deployment_required');
    }

    public function textToAudio(string $text, array $options = []): array
    {
        throw new \RuntimeException('azure_openai_tts_deployment_required');
    }

    public function fineTune(array $options): array
    {
        throw new \RuntimeException('azure_openai_finetune_use_portal');
    }

    public function calculateCost(array $usage): float
    {
        $model = (string)($usage['model'] ?? '');
        $in = (float)($usage['input_tokens'] ?? 0);
        $out = (float)($usage['output_tokens'] ?? 0);
        $pricing = $this->cfg['pricing'][$model] ?? null;
        if (!$pricing) {
            return 0.0;
        }
        $unit = (float)($pricing['token_unit'] ?? 1000);
        $inp = (float)($pricing['input_per_1k'] ?? 0);
        $oup = (float)($pricing['output_per_1k'] ?? 0);
        $cost = ($in / $unit) * $inp + ($out / $unit) * $oup;
        $round = (int)(config('ai.costing.round') ?? 6);
        return round($cost, $round);
    }
}