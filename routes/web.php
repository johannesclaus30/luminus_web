<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/admin/perks', function () {
    return view('admin_perks');
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