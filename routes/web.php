<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
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

// Auth (simple)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin panel (auth-protected)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/status', [AdminController::class, 'status'])->name('status');
    Route::post('/scrape', [AdminController::class, 'trigger'])->name('scrape');
    Route::post('/scrape/football', [AdminController::class, 'scrapeFootball'])->name('scrape.football');
    Route::post('/scrape/fixture', [AdminController::class, 'scrapeFixture'])->name('scrape.fixture');
    Route::get('/leagues/{id}/seasons', [AdminController::class, 'leagueSeasons'])->whereNumber('id')->name('leagues.seasons');
    Route::post('/leagues/toggle', [AdminController::class, 'toggleLeague'])->name('leagues.toggle');
    Route::post('/scrape/stop/{job}', [AdminController::class, 'stop'])->name('scrape.stop');
    Route::post('/news/refresh', [AdminController::class, 'refreshNews'])->name('news.refresh');
});
