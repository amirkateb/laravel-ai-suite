# Laravel AI Suite (Multi‑Provider AI Toolkit for Laravel)

[فارسی](README.md) • [العربية](README.ar.md)

**Maintainer:** [@amirkateb](https://github.com/amirkateb)  
**PHP:** 8.1+ • **Laravel:** 9+

A unified, production‑ready layer for OpenAI, Google Gemini, DeepSeek, xAI Grok, Anthropic, Azure OpenAI, AWS Bedrock, and Ollama:
- Default driver and configurable fallback chain
- Dynamic model listing per provider
- Chat (messages), tool/function calls, embeddings
- OCR via vision chat, image generation, STT and TTS
- Fine‑tuning (on providers that support it)
- Accurate cost calculation from DB/JSON pricing
- Full database logging for every call
- Route‑less developer examples

---

## Install

```bash
composer require amirkateb/laravel-ai-suite
php artisan vendor:publish --provider="AmirKateb\AiSuite\Providers\AiSuiteServiceProvider" --tag=config
php artisan vendor:publish --provider="AmirKateb\AiSuite\Providers\AiSuiteServiceProvider" --tag=migrations
php artisan vendor:publish --provider="AmirKateb\AiSuite\Providers\AiSuiteServiceProvider" --tag=seeders
php artisan migrate
php artisan ai:seed-pricing
```

To seed the full pricing coverage from built‑in JSON files:
```bash
php artisan db:seed --class="AmirKateb\AiSuite\Database\Seeders\AiSuiteFullPricingSeeder"
```

---

## Config

Key options in `config/ai.php`:
- `default` provider name
- `fallback.enabled` and `fallback.order`
- `providers` section with API keys, base URLs, and optional inline pricing

Sample `.env` snippet:
```
# =========================
# AI Suite — Core Settings
# =========================
AI_DEFAULT=openai

# Fallback
AI_FALLBACK_ENABLED=false
AI_FALLBACK_ORDER=openai,google_gemini,deepseek,xai_grok,ollama,anthropic,azure_openai,aws_bedrock

# Conversation history limits
AI_HISTORY_MAX_MESSAGES=50
AI_HISTORY_MAX_TOKENS=32000

# Costing
AI_COST_CURRENCY=USD
AI_COST_ENABLED=true

# Timeouts (seconds)
AI_TIMEOUT_CONNECT=10
AI_TIMEOUT_READ=120

# Retries
AI_RETRY_ENABLED=true
AI_RETRY_TIMES=2
AI_RETRY_SLEEP_MS=250

# =========================
# OpenAI
# =========================
OPENAI_ENABLED=true
OPENAI_API_KEY=sk-xxxxx
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_ORG=
OPENAI_PROJECT=
OPENAI_MODELS_TTL=3600

# =========================
# Google Gemini
# =========================
GEMINI_ENABLED=true
GEMINI_API_KEY=AIzaSyxxxxx
GEMINI_BASE_URL=https://generativelanguage.googleapis.com
GEMINI_MODELS_TTL=3600

# =========================
# DeepSeek
# =========================
DEEPSEEK_ENABLED=true
DEEPSEEK_API_KEY=ds-xxxxx
DEEPSEEK_BASE_URL=https://api.deepseek.com
DEEPSEEK_MODELS_TTL=3600

# =========================
# xAI Grok
# =========================
XAI_ENABLED=true
XAI_API_KEY=xai-xxxxx
XAI_BASE_URL=https://api.x.ai
XAI_MODELS_TTL=3600

# =========================
# Ollama (local)
# =========================
OLLAMA_ENABLED=true
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODELS_TTL=60

# =========================
# Anthropic
# =========================
ANTHROPIC_ENABLED=true
ANTHROPIC_API_KEY=sk-ant-xxxxx
ANTHROPIC_BASE_URL=https://api.anthropic.com
ANTHROPIC_MODELS_TTL=3600

# =========================
# Azure OpenAI
# =========================
AZURE_OPENAI_ENABLED=false
AZURE_OPENAI_API_KEY=
AZURE_OPENAI_ENDPOINT= # e.g. https://your-resource.openai.azure.com
AZURE_OPENAI_DEPLOYMENT= # e.g. gpt-4o
AZURE_OPENAI_API_VERSION=2024-06-01
AZURE_OPENAI_MODELS_TTL=3600

# =========================
# AWS Bedrock
# =========================
BEDROCK_ENABLED=false
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
BEDROCK_MODELS_TTL=3600
```

---

## Database

Tables:
- `ai_suite_logs` — detailed request log (provider, model, status, latency, tokens, cost, request/response payloads)
- `ai_suite_model_prices` — model pricing for cost calculation

Seeders:
- `AiSuitePricingSeeder` (basic)
- `AiSuiteFullPricingSeeder` (reads all JSON under `src/Resources/pricing`)
- Console command: `php artisan ai:seed-pricing`

JSON pricing files share this schema per item:
`provider, model, input_per_1m, output_per_1m, cached_input_per_1m|null, unit, currency, source`.

---

## Usage (No Routes; Dev‑only)

```php
use AmirKateb\AiSuite\AiManager;
$ai = app(AiManager::class);
```

List models:
```php
$ai->listModels();            // default
$ai->listModels('openai');
```

Chat:
```php
$ai->driver('openai')->chat([
  ['role'=>'system','content'=>'You are helpful.'],
  ['role'=>'user','content'=>'Hello!']
], ['model'=>'gpt-4o-mini']);
```

Tools:
```php
$tools = [[
  'type'=>'function',
  'function'=>['name'=>'get_time','description'=>'returns current time','parameters'=>['type'=>'object','properties'=>[]]]
]];
$ai->chat([['role'=>'user','content'=>'What time is it?']], ['model'=>'gpt-4o','tools'=>$tools]);
```

Embeddings:
```php
$ai->embeddings('text', ['model'=>'text-embedding-3-small']);
```

OCR:
```php
$ai->ocr('/abs/image.png', ['model'=>'gpt-4o-mini', 'prompt'=>'Extract text']);
```

Image:
```php
$ai->image(['model'=>'gpt-image-1','prompt'=>'a blue cat','size'=>'1024x1024','n'=>1]);
```

STT / TTS:
```php
$ai->audioToText('/abs/audio.mp3', ['model'=>'gpt-4o-transcribe']);
$ai->textToAudio('hello', ['model'=>'gpt-4o-mini-tts','voice'=>'alloy','format'=>'mp3']);
```

Fine‑tuning:
```php
$ai->driver('openai')->fineTune(['training_file'=>'file-xxxx','model'=>'gpt-4o-mini']);
```

Cost:
```php
use AmirKateb\AiSuite\Support\UsageCalculator;
$r = $ai->chat([['role'=>'user','content'=>'short answer']], ['model'=>'gpt-4o-mini']);
$u = UsageCalculator::parse('openai', $r, ['model'=>'gpt-4o-mini']);
$cost = $ai->calculateCost($u);
```

---

## Logging

Every call via `AiManager` is stored in `ai_suite_logs` with timestamps, token usage and computed cost. Disable or customize by swapping the manager or overriding the `AiLog` model.

---

## Security Notes

No routes are shipped by default. Examples live in code only (Tinker). Validate file paths and sizes in production, and set appropriate timeouts/fallback policies.

---

## License

MIT — (c) 2025, [@amirkateb](https://github.com/amirkateb)
