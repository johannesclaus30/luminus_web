<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerksController;
use App\Http\Controllers\AnnouncementController;

// PERKS
Route::get('/admin/perks', [PerksController::class, 'index'])
    ->name('perks.index');

Route::get('/admin/perks/create', [PerksController::class, 'create'])
    ->name('perks.create');

Route::post('/admin/perks', [PerksController::class, 'store'])
    ->name('perks.store');

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

Route::get('/admin/directory', function () {
    return view('admin_directory');
});

Route::get('/admin/events', function () {
    return view('admin_events');
});

Route::get('/admin/alumni_tracer', function () {
    return view('admin_alumni_tracer');
});

Route::get('/admin/messages', function () {
    return view('admin_messages');
});

Route::get('/admin/settings', function () {
    return view('admin_settings');
});

Route::get('/admin/testing', function () {
    return view('admin_testing');
});

