<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ObjectController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\Registry\AppendixRegistryController;
use App\Http\Controllers\Registry\AuditLogController;
use App\Http\Controllers\Registry\DashboardController as RegistryDashboardController;
use App\Http\Controllers\Registry\DocumentRegistryController;
use App\Http\Controllers\Registry\IncidentRegistryController;
use App\Http\Controllers\Registry\ObjectAttachmentController;
use App\Http\Controllers\Registry\ObjectRegistryController;
use App\Http\Controllers\Registry\TerminalHomeRegistryController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    Route::get('/login', [SessionController::class, 'create'])
        ->middleware('guest')
        ->name('login');

    Route::post('/login', [SessionController::class, 'store'])
        ->middleware(['guest', 'throttle:login']);

    Route::post('/logout', [SessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    Route::get('/objects', [ObjectController::class, 'index'])->name('objects.index');
    Route::get('/object/{slug}', [ObjectController::class, 'show'])->name('objects.show');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{slug}', [DocumentController::class, 'show'])->name('documents.show');

    Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::get('/incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');

    Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel.index');
    Route::get('/personnel/{codename}', [PersonnelController::class, 'show'])->name('personnel.show');

    Route::get('/divisions', [DivisionController::class, 'index'])->name('divisions.index');

    Route::get('/access', [AccessController::class, 'index'])->name('access.index');

    Route::middleware(['auth', 'registry'])
        ->prefix(config('bno.registry_path'))
        ->name('registry.')
        ->group(function () {
            Route::get('/', RegistryDashboardController::class)->name('dashboard');
            Route::get('/objects', [ObjectRegistryController::class, 'index'])->name('objects.index');
            Route::get('/objects/create', [ObjectRegistryController::class, 'create'])->name('objects.create');
            Route::post('/objects', [ObjectRegistryController::class, 'store'])->name('objects.store');
            Route::get('/objects/{object}/edit', [ObjectRegistryController::class, 'edit'])->name('objects.edit');
            Route::put('/objects/{object}', [ObjectRegistryController::class, 'update'])->name('objects.update');
            Route::post('/objects/{object}/attachments', [ObjectAttachmentController::class, 'store'])->name('objects.attachments.store');
            Route::delete('/objects/{object}/attachments/{attachment}', [ObjectAttachmentController::class, 'destroy'])->name('objects.attachments.destroy');
            Route::post('/objects/{object}/appendices', [AppendixRegistryController::class, 'store'])->name('objects.appendices.store');
            Route::put('/objects/{object}/appendices/{appendix}', [AppendixRegistryController::class, 'update'])->name('objects.appendices.update');
            Route::delete('/objects/{object}/appendices/{appendix}', [AppendixRegistryController::class, 'destroy'])->name('objects.appendices.destroy');

            Route::get('/documents', [DocumentRegistryController::class, 'index'])->name('documents.index');
            Route::get('/documents/create', [DocumentRegistryController::class, 'create'])->name('documents.create');
            Route::post('/documents', [DocumentRegistryController::class, 'store'])->name('documents.store');
            Route::get('/documents/{document}/edit', [DocumentRegistryController::class, 'edit'])->name('documents.edit');
            Route::put('/documents/{document}', [DocumentRegistryController::class, 'update'])->name('documents.update');
            Route::delete('/documents/{document}', [DocumentRegistryController::class, 'destroy'])->name('documents.destroy');

            Route::get('/incidents', [IncidentRegistryController::class, 'index'])->name('incidents.index');
            Route::get('/incidents/create', [IncidentRegistryController::class, 'create'])->name('incidents.create');
            Route::post('/incidents', [IncidentRegistryController::class, 'store'])->name('incidents.store');
            Route::get('/incidents/{incident}/edit', [IncidentRegistryController::class, 'edit'])->name('incidents.edit');
            Route::put('/incidents/{incident}', [IncidentRegistryController::class, 'update'])->name('incidents.update');
            Route::delete('/incidents/{incident}', [IncidentRegistryController::class, 'destroy'])->name('incidents.destroy');

            Route::get('/terminal', [TerminalHomeRegistryController::class, 'edit'])->name('terminal.edit');
            Route::put('/terminal', [TerminalHomeRegistryController::class, 'update'])->name('terminal.update');

            Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
        });
});
