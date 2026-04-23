<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerksController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminController;

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLogin'])
        ->name('admin.login');

    Route::post('/login', [AdminController::class, 'authenticate'])
        ->name('admin.login.attempt');

    Route::get('/logout', [AdminController::class, 'logout'])
        ->name('admin.logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/directory', [AdminController::class, 'index'])
            ->name('admin.directory');

        Route::post('/alumni', [AdminController::class, 'storeAlumni'])
            ->name('admin.alumni.store');

        Route::post('/settings', [AdminController::class, 'store'])
            ->name('admin.settings.store');

        Route::put('/settings', [AdminController::class, 'updateProfile'])
            ->name('admin.settings.update');

        Route::get('/events', [EventController::class, 'index'])
            ->name('events.index');

        Route::get('/events/archived', [EventController::class, 'archived'])
            ->name('events.archived');

        Route::get('/events/create', [EventController::class, 'create'])
            ->name('events.create');

        Route::post('/events', [EventController::class, 'store'])
            ->name('events.store');

        Route::get('/events/{event}/edit', [EventController::class, 'edit'])
            ->name('events.edit');

        Route::put('/events/{event}', [EventController::class, 'update'])
            ->name('events.update');

        Route::delete('/events/{event}', [EventController::class, 'destroy'])
            ->name('events.destroy');

        Route::put('/events/{event}/restore', [EventController::class, 'restore'])
            ->name('events.restore');

        Route::get('/perks', [PerksController::class, 'index'])
            ->name('perks.index');

        Route::get('/perks/create', [PerksController::class, 'create'])
            ->name('perks.create');

        Route::post('/perks', [PerksController::class, 'store'])
            ->name('perks.store');

        Route::get('/perks/archived', [PerksController::class, 'archived'])
            ->name('perks.archived');

        Route::put('/perks/{perk}/restore', [PerksController::class, 'restore'])
            ->name('perks.restore');

        Route::delete('/perks/{perk}', [PerksController::class, 'destroy'])
            ->name('perks.destroy');

        Route::get('/perks/{perk}/edit', [PerksController::class, 'edit'])
            ->name('perks.edit');

        Route::put('/perks/{perk}', [PerksController::class, 'update'])
            ->name('perks.update');

        Route::get('/announcements', [AnnouncementController::class, 'index'])
            ->name('announcements.index');

        Route::get('/announcements/archived', [AnnouncementController::class, 'archived'])
            ->name('announcements.archived');

        Route::get('/announcements/create', [AnnouncementController::class, 'create'])
            ->name('announcements.create');

        Route::post('/announcements', [AnnouncementController::class, 'store'])
            ->name('announcements.store');

        Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])
            ->name('announcements.edit');

        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])
            ->name('announcements.update');

        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])
            ->name('announcements.destroy');

        Route::put('/announcements/{announcement}/restore', [AnnouncementController::class, 'restore'])
            ->name('announcements.restore');

        Route::get('/dashboard', function () {
            return view('admin_dashboard');
        });

        Route::get('/alumni_tracer', function () {
            return view('admin_alumni_tracer');
        });

        Route::get('/messages', function () {
            return view('admin_messages');
        });

        Route::get('/settings', [AdminController::class, 'settings'])
            ->name('admin.settings');

    });
});

// OTHER

Route::get('/', function () {
    return view('welcome');
});

