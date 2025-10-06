<?php

namespace AmirKateb\AiSuite\Examples;

use AmirKateb\AiSuite\AiManager;
use AmirKateb\AiSuite\Support\UsageCalculator;

class UsageExamples
{
    public static function listModels(): array
    {
        $ai = app(AiManager::class);
        $default = $ai->listModels();
        $openai = $ai->listModels('openai');
        $gemini = $ai->listModels('google_gemini');
        $deepseek = $ai->listModels('deepseek');
        $grok = $ai->listModels('xai_grok');
        $anthropic = $ai->listModels('anthropic');
        $azure = $ai->listModels('azure_openai');
        $ollama = $ai->listModels('ollama');
        return ['default' => $default,'openai' => $openai,'google_gemini' => $gemini,'deepseek' => $deepseek,'xai_grok' => $grok,'anthropic' => $anthropic,'azure_openai' => $azure,'ollama' => $ollama];
    }

    public static function chatBasic(string $prompt, ?string $driver = null, ?string $model = null): array
    {
        $ai = app(AiManager::class);
        if ($driver) $ai->driver($driver);
        $options = [];
        if ($model) $options['model'] = $model;
        $messages = [['role' => 'system','content' => 'You are helpful.'],['role' => 'user','content' => $prompt]];
        $resp = $ai->chat($messages, $options);
        $usage = UsageCalculator::parse($driver ?: config('ai.default'), $resp, ['model' => $model]);
        $cost = $ai->calculateCost($usage);
        return ['response' => $resp,'usage' => $usage,'cost' => $cost,'currency' => config('ai.costing.currency')];
    }

    public static function chatWithTools(array $tools, array $messages, ?string $driver = null, ?string $model = null): array
    {
        $ai = app(AiManager::class);
        if ($driver) $ai->driver($driver);
        $options = ['tools' => $tools];
        if ($model) $options['model'] = $model;
        $resp = $ai->chat($messages, $options);
        $usage = UsageCalculator::parse($driver ?: config('ai.default'), $resp, ['model' => $model]);
        $cost = $ai->calculateCost($usage);
        return ['response' => $resp,'usage' => $usage,'cost' => $cost,'currency' => config('ai.costing.currency')];
    }

    public static function embeddings(string $text, ?string $driver = null, ?string $model = null): array
    {
        $ai = app(AiManager::class);
        if ($driver) $ai->driver($driver);
        $options = [];
        if ($model) $options['model'] = $model;
        $resp = $ai->embeddings($text, $options);
        return ['response' => $resp];
    }

    public static function ocr(string $imagePath, ?string $driver = null, ?string $model = null, ?string $prompt = null): array
    {
        $ai = app(AiManager::class);
        if ($driver) $ai->driver($driver);
        $options = [];
        if ($model) $options['model'] = $model;
        if ($prompt) $options['prompt'] = $prompt;
        $resp = $ai->ocr($imagePath, $options);
        $usage = UsageCalculator::parse($driver ?: config('ai.default'), $resp, ['model' => $model]);
        $cost = $ai->calculateCost($usage);
        return ['response' => $resp,'usage' => $usage,'cost' => $cost,'currency' => config('ai.costing.currency')];
    }

    public static function imageGenerate(string $prompt, ?string $driver = null, ?string $model = null, string $size = '1024x1024', int $n = 1): array
    {
        $ai = app(AiManager::class);
        if ($driver) $ai->driver($driver);
        $resp = $ai->image(['prompt' => $prompt,'model' => $model,'size' => $size,'n' => $n]);
        return ['response' => $resp];
    }

    public static function audioToText(string $filePath, ?string $driver = null, ?string $model = null): array
    {
        $ai = app(AiManager::class);
        if ($driver) $ai->driver($driver);
        $options = [];
        if ($model) $options['model'] = $model;
        $resp = $ai->audioToText($filePath, $options);
        return ['response' => $resp];
    }

    public static function textToAudio(string $text, ?string $driver = null, ?string $model = null, ?string $voice = null, ?string $format = null): array
    {
        $ai = app(AiManager::class);
        if ($driver) $ai->driver($driver);
        $options = [];
        if ($model) $options['model'] = $model;
        if ($voice) $options['voice'] = $voice;
        if ($format) $options['format'] = $format;
        $resp = $ai->textToAudio($text, $options);
        return ['response' => $resp];
    }

    public static function fineTuneCreate(string $provider, array $options): array
    {
        $ai = app(AiManager::class);
        $ai->driver($provider);
        $resp = $ai->fineTune($options);
        return ['response' => $resp];
    }

    public static function pricingForCall(string $prompt, string $provider, string $model): array
    {
        $ai = app(AiManager::class);
        $ai->driver($provider);
        $messages = [['role' => 'system','content' => 'You are helpful.'],['role' => 'user','content' => $prompt]];
        $resp = $ai->chat($messages, ['model' => $model]);
        $usage = UsageCalculator::parse($provider, $resp, ['model' => $model]);
        $cost = $ai->calculateCost($usage);
        return ['usage' => $usage,'cost' => $cost,'currency' => config('ai.costing.currency'),'response' => $resp];
    }

    public static function fallbackChainChat(array $messages): array
    {
        $ai = app(AiManager::class);
        $resp = $ai->chat($messages, []);
        return ['response' => $resp];
    }

    public static function historyConversation(string $conversationId, array $messages, ?string $provider = null, ?string $model = null): array
    {
        $ai = app(AiManager::class);
        if ($provider) $ai->driver($provider);
        $options = ['conversation_id' => $conversationId];
        if ($model) $options['model'] = $model;
        $resp = $ai->chat($messages, $options);
        return ['response' => $resp];
    }

    public static function runAll(): array
    {
        $out = [];
        $out['models'] = self::listModels();
        $out['chat_openai'] = self::chatBasic('Hello', 'openai', 'gpt-4o-mini');
        $tools = [['type' => 'function','function' => ['name' => 'get_time','description' => 'returns current time','parameters' => ['type' => 'object','properties' => []]]]];
        $msgs = [['role' => 'user','content' => 'What time is it in UTC?']];
        $out['chat_tools'] = self::chatWithTools($tools, $msgs, 'openai', 'gpt-4o');
        $out['embeddings'] = self::embeddings('lorem ipsum', 'openai', 'text-embedding-3-small');
        $out['ocr'] = self::ocr('/path/to/image.png', 'openai', 'gpt-4o-mini', 'Extract text');
        $out['image'] = self::imageGenerate('a blue cat', 'openai', 'gpt-image-1', '1024x1024', 1);
        $out['stt'] = self::audioToText('/path/to/audio.mp3', 'openai', 'gpt-4o-transcribe');
        $out['tts'] = self::textToAudio('hello world', 'openai', 'gpt-4o-mini-tts', 'alloy', 'mp3');
        $out['pricing'] = self::pricingForCall('short answer please', 'openai', 'gpt-4o-mini');
        $conv = 'conv-'.uniqid();
        $histMsgs = [['role' => 'system','content' => 'short answers'],['role' => 'user','content' => 'hi']];
        $out['history1'] = self::historyConversation($conv, $histMsgs, 'openai', 'gpt-4o-mini');
        $histMsgs2 = [['role' => 'user','content' => 'how are you?']];
        $out['history2'] = self::historyConversation($conv, $histMsgs2, 'openai', 'gpt-4o-mini');
        return $out;
    }
}