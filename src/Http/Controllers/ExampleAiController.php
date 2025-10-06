<?php

namespace AmirKateb\AiSuite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use AmirKateb\AiSuite\AiManager;

class ExampleAiController extends Controller
{
    public function form()
    {
        return view('ai-suite.example.form');
    }

    public function chat(Request $request, AiManager $ai)
    {
        $prompt = $request->string('prompt')->toString();
        $driver = $request->string('driver')->toString() ?: null;
        $model  = $request->string('model')->toString() ?: null;

        $options = [];
        if ($model) $options['model'] = $model;
        if ($driver) $ai->driver($driver);

        $response = $ai->chat([
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $prompt]
        ], $options);

        return view('ai-suite.example.result', compact('response', 'prompt', 'driver', 'model'));
    }
}