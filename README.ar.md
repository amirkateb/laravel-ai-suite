# Laravel AI Suite (عدة مزودي ذكاء اصطناعي للارافيل)

[فارسی](README.md) • [English](README.en.md)

**المشرف:** [@amirkateb](https://github.com/amirkateb)  
**PHP:** ‎8.1+ • **Laravel:** ‎9+

طبقة موحّدة للاتصال بـ OpenAI وGoogle Gemini وDeepSeek وxAI Grok وAnthropic وAzure OpenAI وAWS Bedrock وOllama:
- مزوّد افتراضي وسلسلة بديلة fallback قابلة للتهيئة
- جلب ديناميكي لقوائم النماذج
- محادثة وأدوات (function/tool calls) وEmbeddings
- OCR عبر نماذج الرؤية، توليد الصور، STT وTTS
- Fine‑tuning عند المزوّدين الداعمين
- حساب التكلفة بدقة اعتمادًا على أسعار قاعدة البيانات/JSON
- سجلات كاملة في قاعدة البيانات لكل طلب
- أمثلة بدون أي مسارات (Routes)

---

## التثبيت

```bash
composer require amirkateb/laravel-ai-suite
php artisan vendor:publish --provider="AmirKateb\AiSuite\Providers\AiSuiteServiceProvider" --tag=config
php artisan vendor:publish --provider="AmirKateb\AiSuite\Providers\AiSuiteServiceProvider" --tag=migrations
php artisan vendor:publish --provider="AmirKateb\AiSuite\Providers\AiSuiteServiceProvider" --tag=seeders
php artisan migrate
php artisan ai:seed-pricing
```

لتحميل جميع الأسعار من ملفات الـJSON المدمجة:
```bash
php artisan db:seed --class="AmirKateb\AiSuite\Database\Seeders\AiSuiteFullPricingSeeder"
```

---

## الإعداد

ملف `config/ai.php` يحتوي على:
- `default` اسم المزوّد الافتراضي
- `fallback.enabled` و`fallback.order`
- قسم `providers` لمفاتيح API وعناوين الـBase URL وأسعار إضافية اختيارية

مقتطف `.env`:
```
AI_DEFAULT=openai
AI_FALLBACK_ENABLED=false
AI_FALLBACK_ORDER=openai,google_gemini,deepseek,xai_grok,anthropic,azure_openai,aws_bedrock,ollama
OPENAI_API_KEY=...
GEMINI_API_KEY=...
DEEPSEEK_API_KEY=...
XAI_API_KEY=...
ANTHROPIC_API_KEY=...
AZURE_OPENAI_API_KEY=...
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
```

---

## قاعدة البيانات

الجداول:
- `ai_suite_logs` — سجلات كاملة لكل طلب
- `ai_suite_model_prices` — أسعار النماذج

المُحمِّلات (Seeders):
- `AiSuitePricingSeeder` (أساسي)
- `AiSuiteFullPricingSeeder` (يشحن كل JSONs ضمن `src/Resources/pricing`)
- أمر: `php artisan ai:seed-pricing`

صيغة عناصر JSON للأسعار:
`provider, model, input_per_1m, output_per_1m, cached_input_per_1m|null, unit, currency, source`.

---

## الاستخدام (بدون مسارات)

```php
use AmirKateb\AiSuite\AiManager;
$ai = app(AiManager::class);
```

قوائم النماذج:
```php
$ai->listModels();
$ai->listModels('openai');
```

المحادثة:
```php
$ai->driver('openai')->chat([
  ['role'=>'system','content'=>'You are helpful.'],
  ['role'=>'user','content'=>'مرحبا!']
], ['model'=>'gpt-4o-mini']);
```

الأدوات:
```php
$tools = [[
  'type'=>'function',
  'function'=>['name'=>'get_time','description'=>'returns current time','parameters'=>['type'=>'object','properties'=>[]]]
]];
$ai->chat([['role'=>'user','content'=>'ما الوقت الآن؟']], ['model'=>'gpt-4o','tools'=>$tools]);
```

Embeddings:
```php
$ai->embeddings('نص للاختبار', ['model'=>'text-embedding-3-small']);
```

OCR:
```php
$ai->ocr('/abs/image.png', ['model'=>'gpt-4o-mini','prompt'=>'استخرج النص']);
```

الصور:
```php
$ai->image(['model'=>'gpt-image-1','prompt'=>'a blue cat','size'=>'1024x1024','n'=>1]);
```

تحويل الكلام إلى نص / والنص إلى كلام:
```php
$ai->audioToText('/abs/audio.mp3', ['model'=>'gpt-4o-transcribe']);
$ai->textToAudio('hello', ['model'=>'gpt-4o-mini-tts','voice'=>'alloy','format'=>'mp3']);
```

Fine‑tuning:
```php
$ai->driver('openai')->fineTune(['training_file'=>'file-xxxx','model'=>'gpt-4o-mini']);
```

التكلفة:
```php
use AmirKateb\AiSuite\Support\UsageCalculator;
$r = $ai->chat([['role'=>'user','content'=>'short answer']], ['model'=>'gpt-4o-mini']);
$u = UsageCalculator::parse('openai', $r, ['model'=>'gpt-4o-mini']);
$cost = $ai->calculateCost($u);
```

---

## السجلات

كل استدعاء يدوَّن في جدول `ai_suite_logs` مع الطوابع الزمنية والاستهلاك والتكلفة. يمكن إلغاء أو تخصيص السجلات عبر استبدال الـManager أو نموذج `AiLog`.

---

## الترخيص

MIT — (c) 2025, [@amirkateb](https://github.com/amirkateb)