<?php

namespace AmirKateb\AiSuite\Facades;

use Illuminate\Support\Facades\Facade;
use AmirKateb\AiSuite\AiManager;

class Ai extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AiManager::class;
    }
}