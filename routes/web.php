<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeckController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('/login', function () {
    return view('auth.login');
})->name('login.page');

Route::post('/logout', function () {
    auth()->logout();

    return redirect()->route('login.page');
})->name('logout');

// Deck routes
Route::get('/decks/create', [DeckController::class, 'create'])->name('decks.create');
Route::post('/decks', [DeckController::class, 'store'])->name('decks.store');
Route::get('/decks/{deck}', [DeckController::class, 'show'])->name('decks.show');
Route::get('/decks/{deck}/edit', [DeckController::class, 'edit'])->name('decks.edit');
Route::put('/decks/{deck}', [DeckController::class, 'update'])->name('decks.update');
Route::delete('/decks/{deck}', [DeckController::class, 'destroy'])->name('decks.destroy');

