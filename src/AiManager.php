<?php

namespace AmirKateb\AiSuite;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use AmirKateb\AiSuite\Contracts\DriverInterface;
use AmirKateb\AiSuite\Models\AiLog;
use AmirKateb\AiSuite\Models\AiModelPrice;
use AmirKateb\AiSuite\Support\UsageCalculator;

class AiManager
{
    protected array $config;
    protected array $drivers = [];
    protected ?DriverInterface $activeDriver = null;
    protected ?string $activeDriverName = null;
    protected ?string $lastProviderUsed = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->bootDefault();
    }

    protected function bootDefault(): void
    {
        $name = $this->config['default'] ?? 'openai';
        $this->activeDriverName = $name;
        $this->activeDriver = $this->resolveDriver($name);
    }

    protected function resolveDriver(string $name): ?DriverInterface
    {
        $class = $this->config['drivers']['map'][$name] ?? null;
        if (!$class || !class_exists($class)) {
            return null;
        }
        $driverConfig = $this->config['providers'][$name] ?? [];
        return new $class($driverConfig);
    }

    protected function getFallbackChain(): array
    {
        if (!($this->config['fallback']['enabled'] ?? false)) {
            return [];
        }
        return $this->config['fallback']['order'] ?? [];
    }

    protected function tryFallback(callable $callback)
    {
        $chain = $this->getFallbackChain();
        foreach ($chain as $name) {
            $driver = $this->resolveDriver($name);
            if ($driver && ($this->config['providers'][$name]['enabled'] ?? false)) {
                try {
                    $this->lastProviderUsed = $name;
                    return $callback($driver);
                } catch (\Throwable) {
                }
            }
        }
        throw new \RuntimeException('all_providers_failed');
    }

    public function driver(?string $name = null): static
    {
        if ($name) {
            $this->activeDriverName = $name;
            $this->activeDriver = $this->resolveDriver($name);
        }
        return $this;
    }

    public function listModels(?string $driver = null): array
    {
        $targetName = $driver ?: $this->activeDriverName;
        $target = $driver ? $this->resolveDriver($driver) : $this->activeDriver;
        if (!$target) {
            $result = $this->tryFallback(fn($d) => $d->listModels());
            return is_array($result) ? $result : [];
        }
        return Cache::remember('ai_models_'.($targetName ?? 'default'), 3600, fn() => $target->listModels());
    }

    public function chat(array $messages, array $options = []): array
    {
        return $this->callWithLogging('chat', function (DriverInterface $d) use ($messages, $options) {
            return $d->chat($messages, $options);
        }, ['messages' => $messages, 'options' => $options]);
    }

    public function embeddings(string $text, array $options = []): array
    {
        return $this->callWithLogging('embeddings', function (DriverInterface $d) use ($text, $options) {
            return $d->embeddings($text, $options);
        }, ['text' => $text, 'options' => $options]);
    }

    public function ocr(string $imagePath, array $options = []): array
    {
        return $this->callWithLogging('ocr', function (DriverInterface $d) use ($imagePath, $options) {
            return $d->ocr($imagePath, $options);
        }, ['imagePath' => $imagePath, 'options' => $options]);
    }

    public function image(array $options): array
    {
        return $this->callWithLogging('image', function (DriverInterface $d) use ($options) {
            return $d->image($options);
        }, ['options' => $options]);
    }

    public function audioToText(string $filePath, array $options = []): array
    {
        return $this->callWithLogging('audioToText', function (DriverInterface $d) use ($filePath, $options) {
            return $d->audioToText($filePath, $options);
        }, ['filePath' => $filePath, 'options' => $options]);
    }

    public function textToAudio(string $text, array $options = []): array
    {
        return $this->callWithLogging('textToAudio', function (DriverInterface $d) use ($text, $options) {
            return $d->textToAudio($text, $options);
        }, ['text' => $text, 'options' => $options]);
    }

    public function fineTune(array $options): array
    {
        return $this->callWithLogging('fineTune', function (DriverInterface $d) use ($options) {
            return $d->fineTune($options);
        }, ['options' => $options]);
    }

    public function calculateCost(array $usage): float
    {
        $provider = $this->lastProviderUsed ?: $this->activeDriverName;
        $model = (string)($usage['model'] ?? '');
        if ($provider && $model) {
            $row = AiModelPrice::query()->where('provider', $provider)->where('model', $model)->first();
            if ($row) {
                $unit = (int)$row->unit ?: 1000000;
                $inp = (float)$row->input_per_1m;
                $oup = (float)$row->output_per_1m;
                $in = (float)($usage['input_tokens'] ?? 0);
                $out = (float)($usage['output_tokens'] ?? 0);
                $cost = ($in / $unit) * $inp + ($out / $unit) * $oup;
                $round = (int)(config('ai.costing.round') ?? 6);
                return round($cost, $round);
            }
        }
        $driverConfig = $this->config['providers'][$provider] ?? [];
        $pricing = $driverConfig['pricing'][$model] ?? null;
        if (!$pricing) {
            return 0.0;
        }
        $unit = (float)($pricing['token_unit'] ?? 1000);
        $inp = (float)($pricing['input_per_1k'] ?? 0);
        $oup = (float)($pricing['output_per_1k'] ?? 0);
        $in = (float)($usage['input_tokens'] ?? 0);
        $out = (float)($usage['output_tokens'] ?? 0);
        $cost = ($in / $unit) * $inp + ($out / $unit) * $oup;
        $round = (int)(config('ai.costing.round') ?? 6);
        return round($cost, $round);
    }

    public static function make(): static
    {
        return new static(config('ai'));
    }

    protected function callWithLogging(string $operation, callable $fn, array $requestSnapshot): array
    {
        $this->lastProviderUsed = null;
        $provider = $this->activeDriverName;
        $driver = $this->activeDriver;
        $start = microtime(true);
        $startedAt = now();
        $requestId = (string) \Illuminate\Support\Str::uuid();
        $userId = auth()->id() ?? null;
        $ip = Request::ip();
        $conversationId = $requestSnapshot['options']['conversation_id'] ?? null;
        try {
            if (!$driver) {
                $result = $this->tryFallback(function ($d) use ($fn) {
                    return $fn($d);
                });
                $providerUsed = $this->lastProviderUsed ?: $provider;
            } else {
                $result = $fn($driver);
                $providerUsed = $provider;
            }
            $usage = UsageCalculator::parse($providerUsed, is_array($result) ? $result : [], ['model' => $requestSnapshot['options']['model'] ?? null]);
            $cost = $this->calculateCost($usage);
            $finishedAt = now();
            $duration = (int) round((microtime(true) - $start) * 1000);
            AiLog::create([
                'provider' => $providerUsed,
                'driver' => $providerUsed,
                'model' => $usage['model'] ?? ($requestSnapshot['options']['model'] ?? null),
                'operation' => $operation,
                'status' => 'success',
                'error_code' => null,
                'error_message' => null,
                'user_id' => $userId,
                'request_id' => $requestId,
                'conversation_id' => $conversationId,
                'ip' => $ip,
                'duration_ms' => $duration,
                'input_tokens' => $usage['input_tokens'] ?? 0,
                'output_tokens' => $usage['output_tokens'] ?? 0,
                'cost' => $cost,
                'currency' => config('ai.costing.currency', 'USD'),
                'request_payload' => json_encode($requestSnapshot, JSON_UNESCAPED_UNICODE),
                'response_payload' => is_array($result) ? json_encode($result, JSON_UNESCAPED_UNICODE) : (string) $result,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt
            ]);
            return is_array($result) ? $result : ['data' => $result];
        } catch (\Throwable $e) {
            $finishedAt = now();
            $duration = (int) round((microtime(true) - $start) * 1000);
            AiLog::create([
                'provider' => $this->lastProviderUsed ?: $provider,
                'driver' => $this->lastProviderUsed ?: $provider,
                'model' => $requestSnapshot['options']['model'] ?? null,
                'operation' => $operation,
                'status' => 'fail',
                'error_code' => method_exists($e, 'getCode') ? (string)$e->getCode() : null,
                'error_message' => $e->getMessage(),
                'user_id' => $userId,
                'request_id' => $requestId,
                'conversation_id' => $conversationId,
                'ip' => $ip,
                'duration_ms' => $duration,
                'input_tokens' => 0,
                'output_tokens' => 0,
                'cost' => 0,
                'currency' => config('ai.costing.currency', 'USD'),
                'request_payload' => json_encode($requestSnapshot, JSON_UNESCAPED_UNICODE),
                'response_payload' => null,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt
            ]);
            throw $e;
        }
    }
}