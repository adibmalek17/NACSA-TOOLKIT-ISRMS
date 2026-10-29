<?php
use App\Http\Controllers\RiskPreviewController;
use Illuminate\Support\Facades\Route;

// Require this file from routes/web.php AFTER installing application authentication.
// Web middleware retains CSRF protection. Endpoint only calculates; it does not save.
Route::post('/risk/preview', RiskPreviewController::class)
    ->middleware(['auth', 'throttle:60,1'])->name('risk.preview');
