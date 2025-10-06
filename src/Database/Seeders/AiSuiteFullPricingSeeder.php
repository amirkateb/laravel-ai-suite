<?php

namespace AmirKateb\AiSuite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiSuiteFullPricingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $base = __DIR__ . '/../../Resources/pricing';
        $files = glob($base . '/*.json') ?: [];
        $rows = [];
        foreach ($files as $file) {
            $json = json_decode(file_get_contents($file), true);
            if (!is_array($json)) {
                continue;
            }
            foreach ($json as $row) {
                $rows[] = [
                    'provider' => (string)($row['provider'] ?? ''),
                    'model' => (string)($row['model'] ?? ''),
                    'input_per_1m' => isset($row['input_per_1m']) ? (float)$row['input_per_1m'] : 0,
                    'output_per_1m' => isset($row['output_per_1m']) ? (float)$row['output_per_1m'] : 0,
                    'cached_input_per_1m' => isset($row['cached_input_per_1m']) ? (float)$row['cached_input_per_1m'] : null,
                    'unit' => (int)($row['unit'] ?? 1000000),
                    'currency' => (string)($row['currency'] ?? 'USD'),
                    'source' => (string)($row['source'] ?? null),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        if (!empty($rows)) {
            DB::table('ai_suite_model_prices')->upsert(
                $rows,
                ['provider', 'model'],
                ['input_per_1m', 'output_per_1m', 'cached_input_per_1m', 'unit', 'currency', 'source', 'updated_at']
            );
        }
    }
}
