<?php

namespace AmirKateb\AiSuite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use AmirKateb\AiSuite\AiManager;
use AmirKateb\AiSuite\Support\UsageCalculator;

class AiController extends Controller
{
    public function models(Request $request, ?string $driver = null)
    {
        $mgr = AiManager::make();
        $list = $mgr->listModels($driver);
        return response()->json(['driver' => $driver ?? 'default', 'data' => $list]);
    }

    public function chat(Request $request)
    {
        $driver = $request->string('driver')->toString() ?: null;
        $options = (array) $request->input('options', []);
        $messages = (array) $request->input('messages', []);
        $mgr = AiManager::make();
        if ($driver) {
            $mgr->driver($driver);
        }
        $resp = $mgr->chat($messages, $options);
        $provider = $driver ?: config('ai.default');
        $usage = UsageCalculator::parse($provider, $resp, ['model' => $options['model'] ?? null]);
        $cost = $mgr->calculateCost($usage);
        return response()->json([
            'provider' => $provider,
            'request' => ['messages' => $messages, 'options' => $options],
            'response' => $resp,
            'usage' => $usage,
            'cost' => $cost,
            'currency' => config('ai.costing.currency')
        ]);
    }
}