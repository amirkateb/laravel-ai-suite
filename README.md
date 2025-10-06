پکیج جامع اتصال به هوش‌های مصنوعی برای لاراول)

[English](README.en.md) • [العربية](README.ar.md)

**نگهدارنده:** [@amirkateb](https://github.com/amirkateb)  
**حداقل نسخه PHP:** 8.1 • **فریم‌ورک:** Laravel 9+

این پکیج یک لایه‌ی یکپارچه برای کار با چندین ارائه‌دهندهٔ هوش مصنوعی فراهم می‌کند: OpenAI، Google Gemini، DeepSeek، xAI Grok، Anthropic، Azure OpenAI، AWS Bedrock و Ollama (لوکال). امکانات کلیدی:
- انتخاب درایور پیش‌فرض و زنجیره‌ی fallback
- فهرست داینامیک مدل‌ها از هر سرویس
- چت (messages)، ابزارها (function/tool calls)، تع嵌‌گذاری (embeddings)
- OCR از طریق مدل‌های vision/chat، تولید تصویر، STT و TTS
- فاین‌تیونینگ (در سرویس‌هایی که پشتیبانی دارند)
- محاسبه‌ی هزینه‌ی هر درخواست بر اساس قیمت مدل‌ها (DB + JSON)
- لاگ کامل دیتابیسی برای هر فراخوانی
- مثال‌های کامل بدون روت (فقط برای برنامه‌نویسان)

---

## نصب

```
composer require amirkateb/laravel-ai-suite
php artisan vendor:publish --provider="AmirKateb\AiSuite\Providers\AiSuiteServiceProvider" --tag=config
php artisan vendor:publish --provider="AmirKateb\AiSuite\Providers\AiSuiteServiceProvider" --tag=migrations
php artisan vendor:publish --provider="AmirKateb\AiSuite\Providers\AiSuiteServiceProvider" --tag=seeders
php artisan migrate
php artisan ai:seed-pricing
```

> اگر از فایل‌های قیمت JSON داخلی استفاده می‌کنید و می‌خواهید کامل‌ترین پوشش را داشته باشید، از Seeder «کامل» استفاده کنید:
```
php artisan db:seed --class="AmirKateb\AiSuite\Database\Seeders\AiSuiteFullPricingSeeder"
```

---

## تنظیمات

پس از publish، فایل `config/ai.php` ایجاد می‌شود. پارامترهای کلیدی:
- `default`: نام درایور پیش‌فرض (مثلاً `openai`)
- `fallback.enabled`: فعال/غیرفعال بودن زنجیره‌ی جایگزین
- `fallback.order`: ترتیب ارائه‌دهنده‌ها برای تلاش مجدد
- `providers`: کلیدها و تنظیمات هر سرویس (API key، base_url، قیمت‌های دستی)

نمونه `.env`:
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

## دیتابیس

### جداول
- `ai_suite_logs`: ثبت کامل هر فراخوانی شامل provider، مدل، وضعیت، مدت‌زمان، توکن‌های مصرفی، هزینه، payload درخواست/پاسخ.
- `ai_suite_model_prices`: قیمت‌ مدل‌ها برای محاسبه‌ی هزینه.

### Migration‌ها
پس از publish و اجرای `php artisan migrate` دو جدول بالا ساخته می‌شود. تاریخچه و ایندکس‌ها برای کوئری‌گیری سریع لحاظ شده‌اند.

### Seeders قیمت‌ها
- `AiSuitePricingSeeder`: نمونه‌ی مینیمال.
- `AiSuiteFullPricingSeeder`: تمام مدل‌های اصلی را از JSON داخلی می‌خواند.
- فرمان اختصاصی: `php artisan ai:seed-pricing`

### JSON قیمت‌ها
مسیر: `vendor/amirkateb/laravel-ai-suite/src/Resources/pricing/*.json`  
برای هر provider یک فایل JSON شامل آرایه‌ای از رکوردها با کلیدهای:
- `provider`, `model`, `input_per_1m`, `output_per_1m`, `cached_input_per_1m|null`, `unit` (پیش‌فرض 1,000,000), `currency`, `source`

Seeding کامل از این فایل‌ها توسط `AiSuiteFullPricingSeeder` انجام می‌شود.

---

## استفاده (بدون روت؛ مخصوص برنامه‌نویس‌ها)

### Service Resolution
```php
use AmirKateb\AiSuite\AiManager;

$ai = app(AiManager::class);       // یا helper: ai()
$ai->driver('openai');             // تغییر درایور فعال
```

### فهرست مدل‌ها
```php
$modelsDefault = $ai->listModels();
$modelsOpenAI = $ai->listModels('openai');
```

### چت ساده
```php
$resp = $ai->chat([
  ['role' => 'system', 'content' => 'You are helpful.'],
  ['role' => 'user', 'content' => 'سلام!']
], ['model' => 'gpt-4o-mini']);
```

### چت با ابزارها (function/tool calls)
```php
$tools = [[
  'type' => 'function',
  'function' => [
    'name' => 'get_time',
    'description' => 'returns current time',
    'parameters' => ['type' => 'object', 'properties' => []]
  ]
]];
$resp = $ai->chat([['role'=>'user','content'=>'الان ساعت چند است؟']], ['model'=>'gpt-4o','tools'=>$tools]);
```

### Embeddings
```php
$resp = $ai->embeddings('متن برای تع嵌‌گذاری', ['model' => 'text-embedding-3-small']);
```

### OCR (Vision از مسیر تصویر لوکال)
```php
$resp = $ai->ocr(storage_path('app/public/sample.png'), ['model' => 'gpt-4o-mini', 'prompt' => 'متن قابل خواندن را استخراج کن']);
```

### تولید تصویر
```php
$resp = $ai->image(['model' => 'gpt-image-1', 'prompt' => 'a blue cat', 'size' => '1024x1024', 'n' => 1]);
```

### تبدیل گفتار به متن (STT)
```php
$resp = $ai->audioToText(storage_path('app/sample.mp3'), ['model' => 'gpt-4o-transcribe']);
```

### تبدیل متن به گفتار (TTS)
```php
$resp = $ai->textToAudio('سلام دنیا', ['model' => 'gpt-4o-mini-tts', 'voice' => 'alloy', 'format' => 'mp3']);
```

### فاین‌تیونینگ
```php
$resp = $ai->driver('openai')->fineTune([
  'training_file' => 'file-xxxx',
  'model' => 'gpt-4o-mini',
  'hyperparameters' => []
]);
```

### محاسبه‌ی هزینه
```php
use AmirKateb\AiSuite\Support\UsageCalculator;

$resp = $ai->chat([['role'=>'user','content'=>'یک جواب کوتاه بده']], ['model' => 'gpt-4o-mini']);
$usage = UsageCalculator::parse('openai', $resp, ['model' => 'gpt-4o-mini']);
$cost = $ai->calculateCost($usage);
// $cost بر حسب currency تنظیم‌شده در config('ai.costing.currency')
```

### مثال‌های آماده (بدون روت)
در `src/Examples` کلاس‌های `UsageExamples` و `FineTuneExamples` را می‌توانید داخل Tinker اجرا کنید:
```
php artisan tinker
>>> AmirKateb\AiSuite\Examples\UsageExamples::runAll();
```

---

## لاگ کامل

هر فراخوانی از طریق `AiManager` در جدول `ai_suite_logs` ثبت می‌شود:
- شناسهٔ درخواست، کاربر (در صورت Auth)، IP، زمان شروع/پایان، مدت، وضعیت
- ورودی/خروجی توکن‌ها و هزینه
- Payload درخواست و پاسخ (برای دیباگ)

برای خاموش‌کردن لاگینگ کافی است به‌جای `AiManager` پکیج، از یک wrapper خودتان استفاده کنید یا مدل `AiLog` را override کنید.

---

## امنیت

- هیچ Route نمونه‌ای به‌صورت پیش‌فرض فعال نیست.
- مثال‌ها فقط در Tinker/کُد اپلیکیشن استفاده می‌شوند.
- ورودی فایل‌ها را در مسیرهای امن خوانده و سایز فایل‌های صوت/تصویر را کنترل کنید.
- برای محیط Production، نرخ درخواست‌ها، timeouts و fallback را مطابق SLA تنظیم کنید.

---

## توسعه

- افزودن درایور جدید: یک کلاس پیاده‌سازی `Contracts\DriverInterface` بنویسید و در `config('ai.drivers.map')` ثبت کنید.
- سفارشی‌سازی قیمت‌ها: رکوردهای جدول `ai_suite_model_prices` را به‌روزرسانی یا JSONهای `src/Resources/pricing` را ویرایش و مجدد seed کنید.
- تاریخچه مکالمه: اگر نیاز به استوری پایدار دارید، `HistoryStoreInterface` را به‌دلخواه پیاده‌سازی کنید.

---

## لایسنس

MIT — (c) 2025, [@amirkateb](https://github.com/amirkateb)
