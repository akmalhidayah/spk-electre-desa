<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DusunController;
use App\Http\Controllers\Admin\ElectreCalculationController;
use App\Http\Controllers\Admin\HasilRekomendasiController as AdminHasilRekomendasiController;
use App\Http\Controllers\Admin\KriteriaController;
use App\Http\Controllers\Admin\PenilaianAlternatifController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UsulanPembangunanController as AdminUsulanPembangunanController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KepalaDesa\DashboardController as KepalaDesaDashboardController;
use App\Http\Controllers\KepalaDesa\HasilRekomendasiController as KepalaDesaHasilRekomendasiController;
use App\Http\Controllers\KepalaDesa\KeputusanAkhirController as KepalaDesaKeputusanAkhirController;
use App\Http\Controllers\KepalaDusun\DashboardController as KepalaDusunDashboardController;
use App\Http\Controllers\KepalaDusun\UsulanPembangunanController as KepalaDusunUsulanPembangunanController;
use App\Http\Controllers\ProfileController;
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
Route::get('/register', function () {
    return redirect()
        ->route('login')
        ->with('status', 'Pendaftaran akun dilakukan melalui admin desa. Silakan hubungi perangkat desa untuk dibuatkan akun.');
})->name('register');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('/users/{user}/reset-password', [UserController::class, 'resetPasswordForm'])->name('users.reset-password');
        Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/dusuns', [DusunController::class, 'index'])->name('dusuns.index');
        Route::get('/dusuns/create', [DusunController::class, 'create'])->name('dusuns.create');
        Route::post('/dusuns', [DusunController::class, 'store'])->name('dusuns.store');
        Route::get('/dusuns/{dusun}/edit', [DusunController::class, 'edit'])->name('dusuns.edit');
        Route::put('/dusuns/{dusun}', [DusunController::class, 'update'])->name('dusuns.update');
        Route::patch('/dusuns/{dusun}/toggle-status', [DusunController::class, 'toggleStatus'])->name('dusuns.toggle-status');
        Route::delete('/dusuns/{dusun}', [DusunController::class, 'destroy'])->name('dusuns.destroy');

        Route::get('/kriterias', [KriteriaController::class, 'index'])->name('kriterias.index');
        Route::get('/kriterias/create', [KriteriaController::class, 'create'])->name('kriterias.create');
        Route::post('/kriterias', [KriteriaController::class, 'store'])->name('kriterias.store');
        Route::get('/kriterias/{kriteria}/edit', [KriteriaController::class, 'edit'])->name('kriterias.edit');
        Route::put('/kriterias/{kriteria}', [KriteriaController::class, 'update'])->name('kriterias.update');
        Route::patch('/kriterias/{kriteria}/toggle-status', [KriteriaController::class, 'toggleStatus'])->name('kriterias.toggle-status');
        Route::delete('/kriterias/{kriteria}', [KriteriaController::class, 'destroy'])->name('kriterias.destroy');

        Route::get('/usulan-pembangunan', [AdminUsulanPembangunanController::class, 'index'])->name('usulan.index');
        Route::get('/usulan-pembangunan/create', [AdminUsulanPembangunanController::class, 'create'])->name('usulan.create');
        Route::post('/usulan-pembangunan', [AdminUsulanPembangunanController::class, 'store'])->name('usulan.store');
        Route::get('/usulan-pembangunan/{usulanPembangunan}/edit', [AdminUsulanPembangunanController::class, 'edit'])->name('usulan.edit');
        Route::put('/usulan-pembangunan/{usulanPembangunan}', [AdminUsulanPembangunanController::class, 'update'])->name('usulan.update');
        Route::patch('/usulan-pembangunan/{usulanPembangunan}/status', [AdminUsulanPembangunanController::class, 'updateStatus'])->name('usulan.update-status');
        Route::delete('/usulan-pembangunan/{usulanPembangunan}', [AdminUsulanPembangunanController::class, 'destroy'])->name('usulan.destroy');

        Route::get('/penilaian-alternatif', [PenilaianAlternatifController::class, 'index'])->name('penilaian.index');
        Route::post('/penilaian-alternatif', [PenilaianAlternatifController::class, 'store'])->name('penilaian.store');
        Route::get('/penilaian-alternatif/preview', [PenilaianAlternatifController::class, 'preview'])->name('penilaian.preview');

        Route::get('/proses-electre', [ElectreCalculationController::class, 'index'])->name('electre.index');
        Route::post('/proses-electre', [ElectreCalculationController::class, 'process'])->name('electre.process');
        Route::get('/proses-electre/{electreCalculation}', [ElectreCalculationController::class, 'show'])->name('electre.show');
        Route::delete('/proses-electre/{electreCalculation}', [ElectreCalculationController::class, 'destroy'])->name('electre.destroy');

        Route::get('/hasil-rekomendasi', [AdminHasilRekomendasiController::class, 'index'])->name('hasil-rekomendasi.index');
        Route::get('/hasil-rekomendasi/{electreCalculation}', [AdminHasilRekomendasiController::class, 'show'])->name('hasil-rekomendasi.show');
        Route::get('/hasil-rekomendasi/{electreCalculation}/pdf', [AdminHasilRekomendasiController::class, 'pdf'])->name('hasil-rekomendasi.pdf');
    });

Route::prefix('kepala-dusun')
    ->name('kepala-dusun.')
    ->middleware(['auth', 'role:kepala_dusun'])
    ->group(function () {
        Route::get('/dashboard', KepalaDusunDashboardController::class)->name('dashboard');
        Route::get('/usulan', [KepalaDusunUsulanPembangunanController::class, 'index'])->name('usulan.index');
        Route::get('/usulan/create', [KepalaDusunUsulanPembangunanController::class, 'create'])->name('usulan.create');
        Route::post('/usulan', [KepalaDusunUsulanPembangunanController::class, 'store'])->name('usulan.store');
        Route::get('/usulan/{usulanPembangunan}/edit', [KepalaDusunUsulanPembangunanController::class, 'edit'])->name('usulan.edit');
        Route::put('/usulan/{usulanPembangunan}', [KepalaDusunUsulanPembangunanController::class, 'update'])->name('usulan.update');
        Route::delete('/usulan/{usulanPembangunan}', [KepalaDusunUsulanPembangunanController::class, 'destroy'])->name('usulan.destroy');
    });

Route::prefix('kepala-desa')
    ->name('kepala-desa.')
    ->middleware(['auth', 'role:kepala_desa'])
    ->group(function () {
        Route::get('/dashboard', KepalaDesaDashboardController::class)->name('dashboard');
        Route::get('/hasil-rekomendasi', [KepalaDesaHasilRekomendasiController::class, 'index'])->name('hasil-rekomendasi.index');
        Route::get('/hasil-rekomendasi/{electreCalculation}', [KepalaDesaHasilRekomendasiController::class, 'show'])->name('hasil-rekomendasi.show');
        Route::get('/hasil-rekomendasi/{electreCalculation}/pdf', [KepalaDesaHasilRekomendasiController::class, 'pdf'])->name('hasil-rekomendasi.pdf');
        Route::get('/keputusan-akhir', [KepalaDesaKeputusanAkhirController::class, 'index'])->name('keputusan-akhir.index');
        Route::get('/keputusan-akhir/{electreCalculation}/create', [KepalaDesaKeputusanAkhirController::class, 'create'])->name('keputusan-akhir.create');
        Route::post('/keputusan-akhir', [KepalaDesaKeputusanAkhirController::class, 'store'])->name('keputusan-akhir.store');
        Route::get('/keputusan-akhir/{keputusanAkhir}', [KepalaDesaKeputusanAkhirController::class, 'show'])->name('keputusan-akhir.show');
    });
