<?php

use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'index'])->name('news.index');
Route::post('/refresh', [NewsController::class, 'refresh'])->name('news.refresh');
Route::get('/berita/{id}', [NewsController::class, 'show'])
    ->whereNumber('id')
    ->name('news.show');

Route::get('/upload', [ImageUploadController::class, 'create'])->name('upload.create');
Route::post('/upload', [ImageUploadController::class, 'store'])->name('upload.store');
