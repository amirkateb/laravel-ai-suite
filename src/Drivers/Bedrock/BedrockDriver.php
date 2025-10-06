<?php

namespace AmirKateb\AiSuite\Drivers\Bedrock;

use AmirKateb\AiSuite\Contracts\DriverInterface;

class BedrockDriver implements DriverInterface
{
    protected array $cfg;

    public function __construct(array $config)
    {
        $this->cfg = $config;
    }

    protected function region(): string
    {
        return (string)($this->cfg['region'] ?? 'us-east-1');
    }

    protected function accessKey(): string
    {
        return (string)($this->cfg['access_key'] ?? '');
    }

    protected function secretKey(): string
    {
        return (string)($this->cfg['secret_key'] ?? '');
    }

    protected function sessionToken(): ?string
    {
        return $this->cfg['session_token'] ?? null;
    }

    protected function runtimeHost(): string
    {
        return 'bedrock-runtime.'.$this->region().'.amazonaws.com';
    }

    protected function controlHost(): string
    {
        return 'bedrock.'.$this->region().'.amazonaws.com';
    }

    protected function sigv4(string $method, string $host, string $path, string $region, string $service, array $headers, string $payload): array
    {
        $t = gmdate('Ymd\THis\Z');
        $d = substr($t, 0, 8);
        $canonicalHeaders = '';
        $lower = [];
        foreach ($headers as $k => $v) {
            $lk = strtolower($k);
            $lower[$lk] = trim($v);
        }
        $lower['host'] = $host;
        $lower['x-amz-date'] = $t;
        if ($this->sessionToken()) {
            $lower['x-amz-security-token'] = $this->sessionToken();
        }
        ksort($lower);
        foreach ($lower as $k => $v) {
            $canonicalHeaders .= $k.':'.$v."\n";
        }
        $signedHeaders = implode(';', array_keys($lower));
        $hashPayload = hash('sha256', $payload);
        $canonicalRequest = $method."\n".$path."\n"."\n".$canonicalHeaders."\n".$signedHeaders."\n".$hashPayload;
        $scope = $d.'/'.$region.'/'.$service.'/aws4_request';
        $stringToSign = 'AWS4-HMAC-SHA256'."\n".$t."\n".$scope."\n".hash('sha256', $canonicalRequest);
        $kDate = hash_hmac('sha256', $d, 'AWS4'.$this->secretKey(), true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);
        $authorization = 'AWS4-HMAC-SHA256 Credential='.$this->accessKey().'/'.$scope.', SignedHeaders='.$signedHeaders.', Signature='.$signature;
        $lower['authorization'] = $authorization;
        $final = [];
        foreach ($lower as $k => $v) {
            $final[implode('-', array_map('ucfirst', explode('-', $k)))] = $v;
        }
        return $final;
    }

    protected function post(string $host, string $path, string $service, array $body): array
    {
        $payload = json_encode($body, JSON_UNESCAPED_SLASHES);
        $headers = ['Content-Type' => 'application/json'];
        $signed = $this->sigv4('POST', $host, $path, $this->region(), $service, $headers, $payload);
        $url = 'https://'.$host.$path;
        $opts = ['http' => ['method' => 'POST', 'header' => $this->packHeaders($signed), 'content' => $payload, 'ignore_errors' => true]];
        $ctx = stream_context_create($opts);
        $resp = file_get_contents($url, false, $ctx);
        $code = $this->extractStatusCode($http_response_header ?? []);
        if ($resp === false || $code < 200 || $code >= 300) {
            throw new \RuntimeException('bedrock_http_error_'.$code);
        }
        $json = json_decode($resp, true);
        return is_array($json) ? $json : ['raw' => $resp];
    }

    protected function get(string $host, string $path, string $service): array
    {
        $payload = '';
        $headers = [];
        $signed = $this->sigv4('GET', $host, $path, $this->region(), $service, $headers, $payload);
        $url = 'https://'.$host.$path;
        $opts = ['http' => ['method' => 'GET', 'header' => $this->packHeaders($signed), 'ignore_errors' => true]];
        $ctx = stream_context_create($opts);
        $resp = file_get_contents($url, false, $ctx);
        $code = $this->extractStatusCode($http_response_header ?? []);
        if ($resp === false || $code < 200 || $code >= 300) {
            throw new \RuntimeException('bedrock_http_error_'.$code);
        }
        $json = json_decode($resp, true);
        return is_array($json) ? $json : ['raw' => $resp];
    }

    protected function packHeaders(array $headers): string
    {
        $lines = [];
        foreach ($headers as $k => $v) {
            $lines[] = $k.': '.$v;
        }
        return implode("\r\n", $lines);
    }

    protected function extractStatusCode(array $headers): int
    {
        foreach ($headers as $h) {
            if (preg_match('#HTTP/\S+\s+(\d{3})#', $h, $m)) {
                return (int)$m[1];
            }
        }
        return 0;
    }

    public function listModels(): array
    {
        if (!empty($this->cfg['available_models']) && is_array($this->cfg['available_models'])) {
            return $this->cfg['available_models'];
        }
        $path = '/model';
        try {
            $resp = $this->get($this->controlHost(), $path, 'bedrock');
            return $resp['modelSummaries'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function chat(array $messages, array $options = []): array
    {
        $modelId = $options['model_id'] ?? '';
        if ($modelId === '') {
            throw new \InvalidArgumentException('model_id_required');
        }
        $body = $options['body'] ?? null;
        if (!is_array($body)) {
            $prompt = '';
            foreach ($messages as $m) {
                $prompt .= ($m['role'] ?? 'user').": ".(is_array($m['content']) ? json_encode($m['content']) : (string)$m['content'])."\n";
            }
            $body = ['prompt' => $prompt, 'max_tokens' => $options['max_tokens'] ?? 512, 'temperature' => $options['temperature'] ?? 0.7];
        }
        $path = '/model/'.$modelId.'/invoke';
        return $this->post($this->runtimeHost(), $path, 'bedrock', $body);
    }

    public function embeddings(string $text, array $options = []): array
    {
        $modelId = $options['model_id'] ?? '';
        if ($modelId === '') {
            throw new \InvalidArgumentException('model_id_required');
        }
        $body = $options['body'] ?? ['inputText' => $text];
        $path = '/model/'.$modelId.'/invoke';
        return $this->post($this->runtimeHost(), $path, 'bedrock', $body);
    }

    public function ocr(string $imagePath, array $options = []): array
    {
        throw new \RuntimeException('bedrock_ocr_use_vision_model_specific_body');
    }

    public function image(array $options): array
    {
        $modelId = $options['model_id'] ?? '';
        if ($modelId === '') {
            throw new \InvalidArgumentException('model_id_required');
        }
        $body = $options['body'] ?? [];
        $path = '/model/'.$modelId.'/invoke';
        return $this->post($this->runtimeHost(), $path, 'bedrock', $body);
    }

    public function audioToText(string $filePath, array $options = []): array
    {
        throw new \RuntimeException('bedrock_stt_use_model_specific_body');
    }

    public function textToAudio(string $text, array $options = []): array
    {
        throw new \RuntimeException('bedrock_tts_use_model_specific_body');
    }

    public function fineTune(array $options): array
    {
        throw new \RuntimeException('bedrock_finetune_via_studio');
    }

    public function calculateCost(array $usage): float
    {
        $pricing = $this->cfg['pricing'] ?? [];
        $model = $usage['model'] ?? '';
        $in = (float)($usage['input_tokens'] ?? 0);
        $out = (float)($usage['output_tokens'] ?? 0);
        $p = $pricing[$model] ?? null;
        if (!$p) {
            return 0.0;
        }
        $unit = (float)($p['token_unit'] ?? 1000);
        $inp = (float)($p['input_per_1k'] ?? 0);
        $oup = (float)($p['output_per_1k'] ?? 0);
        $cost = ($in / $unit) * $inp + ($out / $unit) * $oup;
        $round = (int)(config('ai.costing.round') ?? 6);
        return round($cost, $round);
    }
}