<?php

use App\Http\Controllers\UserManagementController;
use App\Http\Middleware\EnsureSystemAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'mfa.enrolled', EnsureSystemAdmin::class])
    ->prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::post('/', [UserManagementController::class, 'store'])->middleware('throttle:20,1')->name('store');
        Route::put('/{user}/membership', [UserManagementController::class, 'assign'])
            ->whereNumber('user')->name('assign');
        Route::delete('/{user}/membership/{department}', [UserManagementController::class, 'remove'])
            ->whereNumber('user')->whereNumber('department')->name('remove');
    });
