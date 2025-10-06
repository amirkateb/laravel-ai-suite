<?php

namespace AmirKateb\AiSuite\Support;

class UsageCalculator
{
    public static function parse(string $provider, array $response, array $fallback = []): array
    {
        $model = $fallback['model'] ?? ($response['model'] ?? null);
        $in = 0;
        $out = 0;
        switch ($provider) {
            case 'openai':
            case 'azure_openai':
            case 'deepseek':
            case 'xai_grok':
                if (isset($response['usage'])) {
                    $in = (int) ($response['usage']['prompt_tokens'] ?? 0);
                    $out = (int) ($response['usage']['completion_tokens'] ?? 0);
                }
                if (!$model) {
                    $model = $response['model'] ?? ($fallback['model'] ?? null);
                }
                break;
            case 'anthropic':
                if (isset($response['usage'])) {
                    $in = (int) ($response['usage']['input_tokens'] ?? 0);
                    $out = (int) ($response['usage']['output_tokens'] ?? 0);
                }
                if (!$model) {
                    $model = $response['model'] ?? ($fallback['model'] ?? null);
                }
                break;
            case 'google_gemini':
                if (isset($response['usageMetadata'])) {
                    $in = (int) ($response['usageMetadata']['promptTokenCount'] ?? 0);
                    $out = (int) ($response['usageMetadata']['candidatesTokenCount'] ?? 0);
                }
                if (!$model) {
                    $model = $response['model'] ?? ($fallback['model'] ?? null);
                }
                break;
            case 'ollama':
                $in = 0;
                $out = 0;
                if (!$model) {
                    $model = $response['model'] ?? ($fallback['model'] ?? null);
                }
                break;
            case 'aws_bedrock':
                $in = 0;
                $out = 0;
                if (!$model) {
                    $model = $fallback['model'] ?? null;
                }
                break;
            default:
                $in = 0;
                $out = 0;
                if (!$model) {
                    $model = $fallback['model'] ?? null;
                }
        }
        return [
            'model' => $model,
            'input_tokens' => $in,
            'output_tokens' => $out
        ];
    }
}