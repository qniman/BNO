<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/login', [SessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [SessionController::class, 'store'])->middleware(['guest', 'throttle:login']);
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/search', SearchController::class)->name('search');
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tags.show');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/pages', [Admin\PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/create/{type:key}', [Admin\PageController::class, 'create'])->name('pages.create');
    Route::post('/pages/{type:key}', [Admin\PageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{page}/edit', [Admin\PageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [Admin\PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [Admin\PageController::class, 'destroy'])->name('pages.destroy');
    Route::get('/pages/{page}/revisions', [Admin\PageRevisionController::class, 'index'])->name('pages.revisions');
    Route::get('/pages/{page}/revisions/{revision}', [Admin\PageRevisionController::class, 'show'])->name('pages.revisions.show');
    Route::post('/pages/{page}/revisions/{revision}/restore', [Admin\PageRevisionController::class, 'restore'])->name('pages.revisions.restore');

    Route::get('/lookup/pages', [Admin\LookupController::class, 'pages'])->name('lookup.pages');
    Route::post('/media', [Admin\MediaController::class, 'store'])->name('media.store');

    Route::middleware('can:manage-types')->group(function () {
        Route::get('/types', [Admin\PageTypeController::class, 'index'])->name('types.index');
        Route::get('/types/create', [Admin\PageTypeController::class, 'create'])->name('types.create');
        Route::post('/types', [Admin\PageTypeController::class, 'store'])->name('types.store');
        Route::get('/types/{type}/edit', [Admin\PageTypeController::class, 'edit'])->name('types.edit');
        Route::put('/types/{type}', [Admin\PageTypeController::class, 'update'])->name('types.update');
        Route::delete('/types/{type}', [Admin\PageTypeController::class, 'destroy'])->name('types.destroy');
    });
});

// Разделы и страницы по префиксу типа: /obj, /obj/bno-001. Должны идти последними.
Route::get('/{prefix}', [PageController::class, 'index'])->where('prefix', '[a-z0-9-]+')->name('pages.index');
Route::get('/{prefix}/{slug}', [PageController::class, 'show'])->where(['prefix' => '[a-z0-9-]+', 'slug' => '[a-z0-9-]+'])->name('pages.show');
