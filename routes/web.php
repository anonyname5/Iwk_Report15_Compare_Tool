<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\NormalizationController;

// Main routes
Route::get('/', [ExcelController::class, 'uploadForm'])->name('excel.upload.form');
Route::post('/upload', [ExcelController::class, 'upload'])->name('excel.upload');
Route::get('/results', [ExcelController::class, 'index'])->name('excel.index');

// Comparison routes
Route::get('/view/{id}', [ExcelController::class, 'show'])->name('excel.show');
Route::get('/compare/{comparisonName}', [ExcelController::class, 'compare'])->name('excel.compare');
Route::get('/export/{comparisonName}', [ExcelController::class, 'exportExcel'])->name('excel.export');

// Normalization routes
Route::get('/normalize', [NormalizationController::class, 'showUploadForm']);
Route::post('/normalize', [NormalizationController::class, 'processFile'])
     ->name('normalize.process');