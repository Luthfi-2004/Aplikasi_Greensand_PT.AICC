<?php

use Illuminate\Support\Facades\Route;

// Greensand (JSH)
use App\Http\Controllers\GreensandJshController;
use App\Http\Controllers\GreensandSummaryController;
use App\Http\Controllers\JshStandardController;
use App\Http\Controllers\JshGfnPageController;

// ACE Line
use App\Http\Controllers\AceLineController;
use App\Http\Controllers\AceSummaryController;
use App\Http\Controllers\AceStandardController;
use App\Http\Controllers\AceGfnPageController;

// ========== Dashboard ==========
Route::view('/', 'greensand.dashboard')->name('dashboard');

// ========== Global lookups ==========
Route::get('/lookup/products', [AceLineController::class, 'lookupProducts'])->name('lookup.products');

// ========== Greensand (JSH) ==========
Route::view('/greensand', 'greensand.greensand')->name('greensand.index');
Route::get('/greensand/export', [GreensandJshController::class, 'export'])->name('greensand.export');
Route::get('/greensand/summary', [GreensandSummaryController::class, 'jsh'])->name('greensand.summary');

Route::prefix('greensand')->name('greensand.')->group(function () {
    // DataTables endpoints
    Route::match(['GET', 'POST'], '/data/mm1', [GreensandJshController::class, 'dataMM1'])->name('data.mm1');
    Route::match(['GET', 'POST'], '/data/mm2', [GreensandJshController::class, 'dataMM2'])->name('data.mm2');
    Route::match(['GET', 'POST'], '/data/all', [GreensandJshController::class, 'dataAll'])->name('data.all');

    // Standards
    Route::get('/standards', [JshStandardController::class, 'index'])->name('standards');
    Route::post('/standards', [JshStandardController::class, 'update'])->name('standards.update');

    // CRUD Processes
    Route::post('/processes', [GreensandJshController::class, 'store'])->name('processes.store');
    Route::get('/processes/{id}', [GreensandJshController::class, 'show'])
        ->whereNumber('id')
        ->name('processes.show');
    Route::put('/processes/{id}', [GreensandJshController::class, 'update'])
        ->whereNumber('id')
        ->name('processes.update');
    Route::delete('/processes/{id}', [GreensandJshController::class, 'destroy'])
        ->whereNumber('id')
        ->name('processes.destroy');
});

// ========== JSH GFN ==========
Route::prefix('jsh-gfn')->name('jshgfn.')->group(function () {
    Route::get('/', [JshGfnPageController::class, 'index'])->name('index');
    Route::post('/', [JshGfnPageController::class, 'store'])->name('store');
    Route::put('/update', [JshGfnPageController::class, 'update'])->name('update');
    Route::post('/delete-today', [JshGfnPageController::class, 'deleteTodaySet'])->name('deleteToday');
    Route::get('/export', [JshGfnPageController::class, 'export'])->name('export');
    Route::get('/check-exists', [JshGfnPageController::class, 'check-exists'])->name('check-exists');
});

// ========== ACE Line ==========
Route::view('/ace', 'ace.index')->name('ace.index');

Route::prefix('ace')->name('ace.')->group(function () {
    // DataTables
    Route::get('/data', [AceLineController::class, 'data'])->name('data');

    // Export
    Route::get('/export-template', [AceLineController::class, 'exportTemplate'])->name('export.template');

    // Summary
    Route::get('/summary', AceSummaryController::class)->name('summary');

    // CRUD
    Route::post('/', [AceLineController::class, 'store'])->name('store');
    Route::get('/{id}', [AceLineController::class, 'show'])
        ->whereNumber('id')
        ->name('show');
    Route::put('/{id}', [AceLineController::class, 'update'])
        ->whereNumber('id')
        ->name('update');
    Route::delete('/{id}', [AceLineController::class, 'destroy'])
        ->whereNumber('id')
        ->name('destroy');

    // Standards
    Route::get('/standards', [AceStandardController::class, 'index'])->name('standards');
    Route::post('/standards', [AceStandardController::class, 'update'])->name('standards.update');
});

// ========== ACE GFN ==========
Route::prefix('aceline-gfn')->name('acelinegfn.')->group(function () {
    Route::get('/', [AceGfnPageController::class, 'index'])->name('index');
    Route::post('/store', [AceGfnPageController::class, 'store'])->name('store');
    Route::put('/update', [AceGfnPageController::class, 'update'])->name('update');
    Route::post('/delete-today', [AceGfnPageController::class, 'deleteTodaySet'])->name('deleteToday');
    Route::get('/export', [AceGfnPageController::class, 'export'])->name('export');
    Route::get('/check-exists', [AceGfnPageController::class, 'check-exists'])->name('check-exists');
});