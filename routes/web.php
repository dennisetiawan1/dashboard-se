<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PetugasReferenceController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ReferenceUploadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsahaController;
use App\Http\Controllers\UsahaImportController;

// Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard (viewer & admin)
Route::middleware('auth.dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/usaha', [UsahaController::class, 'index'])
        ->name('usaha');

    Route::post('/usaha/import', [UsahaImportController::class, 'store'])
        ->name('usaha.import.store');
        
    Route::get('/export/usaha', [ExportController::class, 'exportUsaha'])
        ->name('export.usaha');
    Route::get('/export/usaha-grouped', [ExportController::class, 'exportUsahaGrouped'])
        ->name('export.usaha.grouped');
    Route::get('/export', [ExportController::class, 'index'])->name('export');
});

// Upload (admin)
Route::middleware(['auth.dashboard', 'auth.admin'])->group(function () {

    Route::get('/upload', [UploadController::class, 'index'])->name('uploads.index');
    Route::post('/upload', [UploadController::class, 'store'])->name('uploads.store');
    Route::delete('/upload/{upload}', [UploadController::class, 'destroy'])->name('uploads.destroy');

    Route::get('/upload/{upload}/download', [UploadController::class, 'download'])
        ->name('uploads.download');

    Route::post('/upload-referensi-petugas', [PetugasReferenceController::class, 'store'])->name('references.store');

    Route::get('/reference-upload/{referenceUpload}/download', [ReferenceUploadController::class, 'download'])
        ->name('reference-upload.download');

    Route::delete('/reference-upload/{referenceUpload}', [ReferenceUploadController::class, 'destroy']
        )->name('reference-upload.destroy');

    Route::delete('/usaha-upload/{usahaUpload}', [UsahaImportController::class, 'destroy'])
    ->name('usaha.upload.destroy');
});

// Route::middleware(['auth.dashboard', 'auth.admin'])->group(function () {
//     Route::get('/upload', [UploadController::class, 'index'])->name('uploads.index');
//     Route::post('/upload', [UploadController::class, 'store'])->name('uploads.store');
//     Route::delete('/upload/{upload}', [UploadController::class, 'destroy'])->name('uploads.destroy');
//     Route::post('/upload-referensi-petugas', [PetugasReferenceController::class, 'store'])->name('references.store');
// });