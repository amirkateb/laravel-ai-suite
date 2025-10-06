<?php

namespace AmirKateb\AiSuite\Examples;

use Illuminate\Http\UploadedFile;
use AmirKateb\AiSuite\AiManager;
use AmirKateb\AiSuite\Contracts\FineTuneStoreInterface;

class FineTuneExamples
{
    public static function createDatasetFromFiles(array $paths, string $name = 'dataset', array $meta = []): array
    {
        $store = app(FineTuneStoreInterface::class);
        $files = [];
        foreach ($paths as $p) {
            $files[] = new UploadedFile($p, basename($p), null, null, true);
        }
        return $store->createDataset($name, $files, $meta);
    }

    public static function createJobOpenAI(string $datasetId, string $baseModel, array $hyperparams = []): array
    {
        $ai = app(AiManager::class);
        $ai->driver('openai');
        $options = ['training_file' => $datasetId,'model' => $baseModel,'hyperparameters' => $hyperparams];
        return $ai->fineTune($options);
    }

    public static function createJobDeepSeek(array $body): array
    {
        $ai = app(AiManager::class);
        $ai->driver('deepseek');
        return $ai->fineTune($body);
    }
}