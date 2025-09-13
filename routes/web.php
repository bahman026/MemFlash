<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeckController;
use App\Http\Controllers\LevelSelectionController;
use App\Http\Controllers\StaticDeckController;
use App\Http\Controllers\StudyController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::get('/', function () {
    return view('pages.index');
})->name('welcome');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Level selection (requires authentication)
Route::middleware([AuthMiddleware::class])->group(function () {
    Route::get('/level-selection', [LevelSelectionController::class, 'show'])->name('level.selection');
    Route::post('/level-selection', [LevelSelectionController::class, 'store'])->name('level.store');
});

Route::get('/login', function () {
    // If user is already authenticated, redirect to dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
})->name('login.page');

// Protected routes (authentication required)
Route::middleware([AuthMiddleware::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', function () {
        auth()->logout();

        return redirect()->route('welcome');
    })->name('logout');

    // Deck routes
    Route::get('/decks/create', [DeckController::class, 'create'])->name('decks.create');
    Route::post('/decks', [DeckController::class, 'store'])->name('decks.store');
    Route::get('/decks/{deck}', [DeckController::class, 'show'])->name('decks.show');
    Route::get('/decks/{deck}/edit', [DeckController::class, 'edit'])->name('decks.edit');
    Route::put('/decks/{deck}', [DeckController::class, 'update'])->name('decks.update');
    Route::delete('/decks/{deck}', [DeckController::class, 'destroy'])->name('decks.destroy');
    Route::post('/decks/{deck}/reset', [DeckController::class, 'reset'])->name('decks.reset');

    // Study routes
    Route::get('/study/{deck}', [StudyController::class, 'start'])->name('study.start');
    Route::get('/api/study/{deck}/cards', [StudyController::class, 'getCards'])->name('study.cards');
    Route::post('/api/study/cards/{card}', [StudyController::class, 'updateCard'])->name('study.update-card');
    Route::post('/api/study/batch-update', [StudyController::class, 'batchUpdate'])->name('study.batch-update');

    // Static deck routes
    Route::get('/static-decks/{staticDeck}', [StaticDeckController::class, 'show'])->name('static-decks.show');
    Route::get('/static-decks/{staticDeck}/study', [StaticDeckController::class, 'study'])->name('static-decks.study');
    Route::get('/static-decks/{staticDeck}/preview', [StaticDeckController::class, 'preview'])->name('static-decks.preview');
    Route::post('/static-decks/{staticDeck}/reset', [StaticDeckController::class, 'reset'])->name('static-decks.reset');
    Route::post('/static-decks/{staticDeck}/cards-per-day', [StaticDeckController::class, 'updateCardsPerDay'])->name('static-decks.cards-per-day');

    // Static deck study API routes
    Route::get('/api/static-study/{staticDeck}/cards', [StaticDeckController::class, 'getCards'])->name('static-study.cards');
    Route::post('/api/static-study/cards/{card}', [StaticDeckController::class, 'updateCard'])->name('static-study.update-card');
    Route::post('/api/static-study/batch-update', [StaticDeckController::class, 'batchUpdate'])->name('static-study.batch-update');
});
