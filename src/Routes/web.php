<?php

use Illuminate\Support\Facades\Route;
use AmirKateb\AiSuite\Http\Controllers\AiController;

Route::prefix('ai-suite')->group(function () {
    Route::get('models/{driver?}', [AiController::class, 'models'])->name('ai-suite.models');
    Route::post('chat', [AiController::class, 'chat'])->name('ai-suite.chat');
});