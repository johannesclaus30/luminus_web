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