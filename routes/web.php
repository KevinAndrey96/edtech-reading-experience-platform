<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CreateEvidenceController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\StoreEvidenceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'show'])->name('welcome');

Auth::routes();
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

Route::middleware('auth')->group(function () {
    // Dashboard & Home
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Users Management
    Route::resource('users', UserController::class);

    // Regions Management
    Route::resource('regions', RegionController::class);
    Route::get('/regions/show/{regionID}', [RegionController::class, 'show'])->name('regions.show1');

    // Books & Reading Catalog
    Route::get('/books/region/{regionID}', [BookController::class, 'booksByRegionID'])->name('books.by-region');
    Route::get('/books/evidences/{bookID}', [BookController::class, 'evidencesByBookID'])->name('books.evidences');
    Route::resource('books', BookController::class);

    // Pedagogical Experiences
    Route::resource('experiences', ExperienceController::class);

    // Student Learning Evidences
    Route::get('/evidences/region/{regionID}', [EvidenceController::class, 'evidencesByRegionID'])->name('evidences.by-region');
    Route::get('/evidences-create/book/{bookID}', [EvidenceController::class, 'evidencesByBookID'])->name('evidences.by-book');
    Route::resource('evidences', EvidenceController::class);

    // Legacy and helper aliases for evidence submission forms
    Route::post('/evidence-store', [StoreEvidenceController::class, 'store'])->name('evidence.store');
    Route::post('/evidences-store', [StoreEvidenceController::class, 'store'])->name('evidences.store.alt');
    Route::get('/evidence-create', [CreateEvidenceController::class, 'create'])->name('evidence.create.alt');
});
