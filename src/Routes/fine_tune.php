<?php

use Illuminate\Support\Facades\Route;
use AmirKateb\AiSuite\Http\Controllers\FineTuneController;

Route::prefix('ai-suite/fine-tune')->group(function () {
    Route::post('datasets', [FineTuneController::class, 'createDataset'])->name('ai-suite.ft.datasets.create');
    Route::get('datasets', [FineTuneController::class, 'listDatasets'])->name('ai-suite.ft.datasets.index');
    Route::delete('datasets/{id}', [FineTuneController::class, 'deleteDataset'])->name('ai-suite.ft.datasets.delete');

    Route::post('jobs', [FineTuneController::class, 'createJob'])->name('ai-suite.ft.jobs.create');
    Route::get('jobs', [FineTuneController::class, 'listJobs'])->name('ai-suite.ft.jobs.index');
})
->middleware(['web']);