<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiSuitePricingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $rows = [
            ['provider' => 'openai', 'model' => 'gpt-4o', 'input_per_1m' => 5.00, 'output_per_1m' => 15.00, 'cached_input_per_1m' => null, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'openai_api_pricing', 'updated_at' => $now, 'created_at' => $now],
            ['provider' => 'openai', 'model' => 'gpt-4o-mini', 'input_per_1m' => 0.15, 'output_per_1m' => 0.60, 'cached_input_per_1m' => null, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'openai_gpt4o_mini_blog', 'updated_at' => $now, 'created_at' => $now],

            ['provider' => 'anthropic', 'model' => 'claude-3-5-sonnet', 'input_per_1m' => 3.00, 'output_per_1m' => 15.00, 'cached_input_per_1m' => null, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'anthropic_blog_claude35_sonnet', 'updated_at' => $now, 'created_at' => $now],

            ['provider' => 'google_gemini', 'model' => 'gemini-2.5-flash-lite', 'input_per_1m' => 0.10, 'output_per_1m' => 0.40, 'cached_input_per_1m' => null, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'google_docs_gemini_pricing_flash_lite', 'updated_at' => $now, 'created_at' => $now],

            ['provider' => 'deepseek', 'model' => 'deepseek-chat', 'input_per_1m' => 0.28, 'output_per_1m' => 0.42, 'cached_input_per_1m' => 0.028, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'deepseek_api_pricing', 'updated_at' => $now, 'created_at' => $now],
            ['provider' => 'deepseek', 'model' => 'deepseek-reasoner', 'input_per_1m' => 0.28, 'output_per_1m' => 0.42, 'cached_input_per_1m' => 0.028, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'deepseek_api_pricing', 'updated_at' => $now, 'created_at' => $now],

            ['provider' => 'xai_grok', 'model' => 'grok-4', 'input_per_1m' => 3.00, 'output_per_1m' => 15.00, 'cached_input_per_1m' => null, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'xai_docs_models', 'updated_at' => $now, 'created_at' => $now],
            ['provider' => 'xai_grok', 'model' => 'grok-4-fast-non-reasoning', 'input_per_1m' => 0.20, 'output_per_1m' => 0.50, 'cached_input_per_1m' => null, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'xai_docs_models', 'updated_at' => $now, 'created_at' => $now],
            ['provider' => 'xai_grok', 'model' => 'grok-4-fast-reasoning', 'input_per_1m' => 0.20, 'output_per_1m' => 0.50, 'cached_input_per_1m' => null, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'xai_docs_models', 'updated_at' => $now, 'created_at' => $now],
            ['provider' => 'xai_grok', 'model' => 'grok-code-fast-1', 'input_per_1m' => 0.20, 'output_per_1m' => 1.50, 'cached_input_per_1m' => null, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'xai_docs_models', 'updated_at' => $now, 'created_at' => $now],

            ['provider' => 'ollama', 'model' => 'generic', 'input_per_1m' => 0.00, 'output_per_1m' => 0.00, 'cached_input_per_1m' => null, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'local_llm', 'updated_at' => $now, 'created_at' => $now],

            ['provider' => 'azure_openai', 'model' => 'gpt-4.1', 'input_per_1m' => 5.00, 'output_per_1m' => 15.00, 'cached_input_per_1m' => 1.25, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'azure_openai_pricing', 'updated_at' => $now, 'created_at' => $now],
            ['provider' => 'azure_openai', 'model' => 'gpt-4.1-mini', 'input_per_1m' => 0.80, 'output_per_1m' => 3.20, 'cached_input_per_1m' => 0.20, 'unit' => 1000000, 'currency' => 'USD', 'source' => 'azure_openai_pricing', 'updated_at' => $now, 'created_at' => $now]
        ];

        DB::table('ai_suite_model_prices')->upsert(
            $rows,
            ['provider', 'model'],
            ['input_per_1m', 'output_per_1m', 'cached_input_per_1m', 'unit', 'currency', 'source', 'updated_at']
        );
    }
}