<?php

use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'index'])->name('news.index');
Route::post('/refresh', [NewsController::class, 'refresh'])->name('news.refresh');
Route::get('/berita/{id}', [NewsController::class, 'show'])
    ->whereNumber('id')
    ->name('news.show');
