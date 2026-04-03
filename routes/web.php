<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerksController;

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

Route::get('/admin/announcements', function () {
    return view('admin_announcements');
});

Route::get('/admin/events', function () {
    return view('admin_events');
});


// PERKS
Route::get('/admin/perks', [PerksController::class, 'index'])
    ->name('perks.index');

Route::get('/admin/perks/create', [PerksController::class, 'create'])
    ->name('perks.create');

Route::post('/admin/perks', [PerksController::class, 'store'])
    ->name('perks.store');

// OTHER

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

// Route::get('/login', function () {
//     return view('admin_login');
// })->name('login');
