<?php

use Illuminate\Support\Facades\Route;
use AmirKateb\AiSuite\Http\Controllers\ExampleAiController;

Route::prefix('ai-suite/example')->group(function () {
    Route::get('/', [ExampleAiController::class, 'form'])->name('ai-suite.example.form');
    Route::post('/chat', [ExampleAiController::class, 'chat'])->name('ai-suite.example.chat');
});