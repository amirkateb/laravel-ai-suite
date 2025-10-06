<?php

namespace Tests\Feature;

use Orchestra\Testbench\TestCase;
use AmirKateb\AiSuite\AiManager;
use AmirKateb\AiSuite\Providers\AiSuiteServiceProvider;

class AiSuiteSmokeTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [AiSuiteServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('ai.default', 'openai');
        $app['config']->set('ai.fallback.enabled', false);
        $app['config']->set('ai.providers.openai.api_key', 'test');
        $app['config']->set('ai.providers.openai.base_url', 'https://api.openai.com/v1');
    }

    public function test_manager_constructs()
    {
        $m = AiManager::make();
        $this->assertNotNull($m);
        $this->assertIsArray($m->listModels('ollama') ?? []);
    }
}