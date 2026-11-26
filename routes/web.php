<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])->name('welcome');
Route::get('/regions/show/{regionID}', [RegionController::class, 'show'])->name('regions.show1');

Auth::routes(['register' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::middleware('can:admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::resource('regions', RegionController::class)->except('show');
        Route::resource('books', BookController::class)->except('show');
        Route::get('/books/evidences/{bookID}', [BookController::class, 'evidencesByBookID'])->name('books.evidences');
        Route::get('/evidences/region/{regionID}', [EvidenceController::class, 'evidencesByRegionID'])->name('evidences.by-region');
        Route::resource('evidences', EvidenceController::class)->only(['index', 'edit', 'update', 'destroy']);
    });

    Route::get('/books/region/{regionID}', [BookController::class, 'booksByRegionID'])->name('books.by-region');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

    Route::resource('experiences', ExperienceController::class)->except('create');

    Route::get('/evidences-create/book/{bookID}', [EvidenceController::class, 'evidencesByBookID'])->name('evidences.by-book');
    Route::resource('evidences', EvidenceController::class)->only(['create', 'store', 'show']);
});
