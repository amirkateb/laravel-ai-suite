<?php

use AmirKateb\AiSuite\AiManager;

if (!function_exists('ai')) {
    function ai(): AiManager
    {
        return app(AiManager::class);
    }
}