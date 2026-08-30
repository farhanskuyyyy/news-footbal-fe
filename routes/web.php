<?php

use App\Http\Controllers\FootballController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'index'])->name('news.index');
Route::post('/refresh', [NewsController::class, 'refresh'])->name('news.refresh');
Route::get('/berita/{id}', [NewsController::class, 'show'])
    ->whereNumber('id')
    ->name('news.show');

// Football Portal Routes
Route::prefix('football')->name('football.')->group(function () {
    Route::get('/', [FootballController::class, 'index'])->name('index');
    Route::get('/live', [FootballController::class, 'live'])->name('live');
    Route::get('/matches', [FootballController::class, 'matchday'])->name('matchday');
    Route::get('/search', [FootballController::class, 'search'])->name('search');
    Route::get('/fixtures/{id}', [FootballController::class, 'fixtureDetail'])->whereNumber('id')->name('fixture');
    Route::post('/fixtures/{id}/prepare', [FootballController::class, 'prepareFixture'])->whereNumber('id')->name('fixture.prepare');
    Route::get('/teams/{id}', [FootballController::class, 'teamDetail'])->whereNumber('id')->name('team');
    Route::get('/players/{id}', [FootballController::class, 'playerDetail'])->whereNumber('id')->name('player');
});

Route::get('/upload', [ImageUploadController::class, 'create'])->name('upload.create');
Route::post('/upload', [ImageUploadController::class, 'store'])->name('upload.store');
