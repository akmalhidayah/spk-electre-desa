<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DusunController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KepalaDesa\DashboardController as KepalaDesaDashboardController;
use App\Http\Controllers\KepalaDusun\DashboardController as KepalaDusunDashboardController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return match (auth()->user()->role) {
        User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
        User::ROLE_KEPALA_DUSUN => redirect()->route('kepala-dusun.dashboard'),
        User::ROLE_KEPALA_DESA => redirect()->route('kepala-desa.dashboard'),
        default => redirect()->route('login'),
    };
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.process');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::get('/dusuns', [DusunController::class, 'index'])->name('dusuns.index');
        Route::get('/dusuns/create', [DusunController::class, 'create'])->name('dusuns.create');
        Route::post('/dusuns', [DusunController::class, 'store'])->name('dusuns.store');
        Route::get('/dusuns/{dusun}/edit', [DusunController::class, 'edit'])->name('dusuns.edit');
        Route::put('/dusuns/{dusun}', [DusunController::class, 'update'])->name('dusuns.update');
        Route::patch('/dusuns/{dusun}/toggle-status', [DusunController::class, 'toggleStatus'])->name('dusuns.toggle-status');
        Route::delete('/dusuns/{dusun}', [DusunController::class, 'destroy'])->name('dusuns.destroy');
    });

Route::prefix('kepala-dusun')
    ->name('kepala-dusun.')
    ->middleware(['auth', 'role:kepala_dusun'])
    ->group(function () {
        Route::get('/dashboard', KepalaDusunDashboardController::class)->name('dashboard');
    });

Route::prefix('kepala-desa')
    ->name('kepala-desa.')
    ->middleware(['auth', 'role:kepala_desa'])
    ->group(function () {
        Route::get('/dashboard', KepalaDesaDashboardController::class)->name('dashboard');
    });
