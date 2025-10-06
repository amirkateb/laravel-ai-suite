<?php

namespace AmirKateb\AiSuite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use AmirKateb\AiSuite\AiManager;
use AmirKateb\AiSuite\Contracts\FineTuneStoreInterface;

class FineTuneController extends Controller
{
    public function createDataset(Request $request, FineTuneStoreInterface $store)
    {
        $name = $request->string('name')->toString() ?: 'dataset';
        $meta = (array) $request->input('meta', []);
        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $f) $files[] = $f;
        }
        $dataset = $store->createDataset($name, $files, $meta);
        return response()->json($dataset);
    }

    public function listDatasets(FineTuneStoreInterface $store)
    {
        return response()->json($store->listDatasets());
    }

    public function deleteDataset(string $id, FineTuneStoreInterface $store)
    {
        $ok = $store->deleteDataset($id);
        return response()->json(['deleted' => $ok]);
    }

    public function createJob(Request $request, FineTuneStoreInterface $store, AiManager $ai)
    {
        $provider = $request->string('provider')->toString() ?: config('ai.default');
        $options = (array) $request->input('options', []);
        $job = $store->createJob($provider, $options);
        try {
            $ai->driver($provider)->fineTune($options);
        } catch (\Throwable $e) {
        }
        return response()->json($job);
    }

    public function listJobs(FineTuneStoreInterface $store)
    {
        return response()->json($store->listJobs());
    }
}