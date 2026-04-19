<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerksController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminController;

// ADMINS
Route::get('/admin/directory', [AdminController::class, 'index']);

Route::post('/admin/settings', [AdminController::class, 'store'])
    ->name('admin.settings.store');

// EVENTS
Route::get('/admin/events', [EventController::class, 'index'])
    ->name('events.index');

Route::get('/admin/events/create', [EventController::class, 'create'])
    ->name('events.create');

Route::post('/admin/events', [EventController::class, 'store'])
    ->name('events.store');

Route::get('/admin/events/{event}/edit', [EventController::class, 'edit'])
    ->name('events.edit');

Route::put('/admin/events/{event}', [EventController::class, 'update'])
    ->name('events.update');

// Add this specific line to enable the "Archive" button
Route::delete('/admin/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    

// PERKS
Route::get('/admin/perks', [PerksController::class, 'index'])
    ->name('perks.index');

Route::get('/admin/perks/create', [PerksController::class, 'create'])
    ->name('perks.create');

Route::post('/admin/perks', [PerksController::class, 'store'])
    ->name('perks.store');

// View archived perks
Route::get('/admin/perks/archived', [PerksController::class, 'archived'])
    ->name('perks.archived');

// Restore (unarchive) a perk
Route::put('/admin/perks/{perk}/restore', [PerksController::class, 'restore'])
    ->name('perks.restore');

// Delete (archive) a perk
Route::delete('/admin/perks/{perk}', [PerksController::class, 'destroy'])
    ->name('perks.destroy');

Route::get('/perks/{perk}/edit', [PerksController::class, 'edit'])
    ->name('perks.edit');
    
Route::put('/perks/{perk}', [PerksController::class, 'update'])
    ->name('perks.update');

// ANNOUNCEMENTS
Route::get('/admin/announcements', [AnnouncementController::class, 'index'])
    ->name('announcements.index');

Route::get('/admin/announcements/create', [AnnouncementController::class, 'create'])
    ->name('announcements.create');

Route::post('/admin/announcements', [AnnouncementController::class, 'store'])
    ->name('announcements.store');

    // Show the edit form
Route::get('/admin/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])
    ->name('announcements.edit');

// Process the update (use PUT or PATCH)
Route::put('/admin/announcements/{announcement}', [AnnouncementController::class, 'update'])
    ->name('announcements.update');


    // OTHER

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/login', function () {
    return view('admin_login');
});

Route::get('/admin/dashboard', function () {
    return view('admin_dashboard');
});

// Route::get('/admin/directory', function () {
//     return view('admin_directory');
// });

Route::get('/admin/alumni_tracer', function () {
    return view('admin_alumni_tracer');
});

Route::get('/admin/messages', function () {
    return view('admin_messages');
});

Route::get('/admin/settings', function () {
    return view('admin_settings');
})->name('admin.settings');

Route::get('/admin/testing', function () {
    return view('admin_testing');
});

