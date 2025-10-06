<?php

namespace AmirKateb\AiSuite;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use AmirKateb\AiSuite\Contracts\DriverInterface;

class AiManager
{
    protected array $config;
    protected array $drivers = [];
    protected ?DriverInterface $activeDriver = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->bootDefault();
    }

    protected function bootDefault(): void
    {
        $name = $this->config['default'] ?? 'openai';
        $this->activeDriver = $this->resolveDriver($name);
    }

    protected function resolveDriver(string $name): ?DriverInterface
    {
        $map = $this->config['drivers']['map'][$name] ?? null;
        if (!$map || !class_exists($map)) {
            return null;
        }
        $driverConfig = $this->config['providers'][$name] ?? [];
        return new $map($driverConfig);
    }

    protected function getFallbackChain(): array
    {
        if (!($this->config['fallback']['enabled'] ?? false)) {
            return [];
        }
        return $this->config['fallback']['order'] ?? [];
    }

    protected function tryFallback(callable $callback, string $method, ...$args)
    {
        $chain = $this->getFallbackChain();
        foreach ($chain as $name) {
            $driver = $this->resolveDriver($name);
            if ($driver && $this->isDriverEnabled($name)) {
                try {
                    return $callback($driver, ...$args);
                } catch (\Throwable) {
                }
            }
        }
        throw new \RuntimeException("All AI providers failed for method {$method}");
    }

    protected function isDriverEnabled(string $name): bool
    {
        return $this->config['providers'][$name]['enabled'] ?? false;
    }

    public function driver(?string $name = null): static
    {
        if ($name) {
            $this->activeDriver = $this->resolveDriver($name);
        }
        return $this;
    }

    public function listModels(?string $driver = null): array
    {
        $target = $driver ? $this->resolveDriver($driver) : $this->activeDriver;
        if (!$target) {
            return $this->tryFallback(fn($d) => $d->listModels(), __FUNCTION__);
        }
        return Cache::remember("ai_models_" . ($driver ?? 'default'), 3600, fn() => $target->listModels());
    }

    public function chat(array $messages, array $options = []): array
    {
        try {
            return $this->activeDriver->chat($messages, $options);
        } catch (\Throwable) {
            return $this->tryFallback(fn($d) => $d->chat($messages, $options), __FUNCTION__, $messages, $options);
        }
    }

    public function embeddings(string $text, array $options = []): array
    {
        try {
            return $this->activeDriver->embeddings($text, $options);
        } catch (\Throwable) {
            return $this->tryFallback(fn($d) => $d->embeddings($text, $options), __FUNCTION__, $text, $options);
        }
    }

    public function ocr(string $imagePath, array $options = []): array
    {
        try {
            return $this->activeDriver->ocr($imagePath, $options);
        } catch (\Throwable) {
            return $this->tryFallback(fn($d) => $d->ocr($imagePath, $options), __FUNCTION__, $imagePath, $options);
        }
    }

    public function image(array $options): array
    {
        try {
            return $this->activeDriver->image($options);
        } catch (\Throwable) {
            return $this->tryFallback(fn($d) => $d->image($options), __FUNCTION__, $options);
        }
    }

    public function audioToText(string $filePath, array $options = []): array
    {
        try {
            return $this->activeDriver->audioToText($filePath, $options);
        } catch (\Throwable) {
            return $this->tryFallback(fn($d) => $d->audioToText($filePath, $options), __FUNCTION__, $filePath, $options);
        }
    }

    public function textToAudio(string $text, array $options = []): array
    {
        try {
            return $this->activeDriver->textToAudio($text, $options);
        } catch (\Throwable) {
            return $this->tryFallback(fn($d) => $d->textToAudio($text, $options), __FUNCTION__, $text, $options);
        }
    }

    public function fineTune(array $options): array
    {
        try {
            return $this->activeDriver->fineTune($options);
        } catch (\Throwable) {
            return $this->tryFallback(fn($d) => $d->fineTune($options), __FUNCTION__, $options);
        }
    }

    public function calculateCost(array $usage): float
    {
        return $this->activeDriver->calculateCost($usage);
    }

    public static function make(): static
    {
        return new static(config('ai'));
    }
}