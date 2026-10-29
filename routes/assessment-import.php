<?php

use App\Http\Controllers\RiskAssessmentImportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'mfa.enrolled'])->group(function () {
    // Old navigation links lead to the editable form. Bulk preview/commit routes are retired.
    Route::get('/assessments/import', [RiskAssessmentImportController::class, 'index'])
        ->name('assessments.import');
    Route::get('/assessments/import/template', [RiskAssessmentImportController::class, 'template'])
        ->name('assessments.import.template');
    Route::post('/assessments/import/fill', [RiskAssessmentImportController::class, 'fill'])
        ->middleware('throttle:10,1')->name('assessments.import.fill');
});
