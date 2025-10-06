<?php

namespace AmirKateb\AiSuite\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use AmirKateb\AiSuite\AiManager;

class SetAiDriverFromHeader
{
    public function handle(Request $request, Closure $next)
    {
        $driver = $request->header('X-AI-Driver');
        if (is_string($driver) && $driver !== '') {
            $mgr = app(AiManager::class);
            $mgr->driver($driver);
        }
        return $next($request);
    }
}