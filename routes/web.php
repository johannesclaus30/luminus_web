<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerksController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TracerFormController;
use App\Http\Controllers\AdminDashboardController;

Route::prefix('admin')->group(function () {
    
    // Public Admin Routes (Login)
    Route::get('/login', [AdminController::class, 'showLogin'])
        ->name('admin.login');

    Route::post('/login', [AdminController::class, 'authenticate'])
        ->name('admin.login.attempt');

    Route::get('/logout', [AdminController::class, 'logout'])
        ->name('admin.logout');

    // Forgot Password Routes (Public)
    Route::get('/forgot-password', [AdminController::class, 'showForgotPassword'])->name('admin.forgot-password');
    Route::post('/forgot-password', [AdminController::class, 'sendResetLink'])->name('admin.send-reset-link');
    Route::get('/reset-password', [AdminController::class, 'showResetForm'])->name('admin.reset-password');
    Route::post('/reset-password', [AdminController::class, 'resetPassword'])->name('admin.reset-password.process');

    // 🔹 Protected Admin Routes
    Route::middleware('admin.auth')->group(function () {
        
        // ✅ Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('admin.dashboard');

        // Directory & Settings
        Route::get('/directory', [AdminController::class, 'index'])
            ->name('admin.directory');

        Route::get('/directory/archived', [AdminController::class, 'archived'])
            ->name('admin.directory.archived');

        Route::post('/alumni', [AdminController::class, 'storeAlumni'])
            ->name('admin.alumni.store');

        // Alumni CRUD
        Route::get('/alumni/{id}/edit', [AdminController::class, 'editAlumni'])
            ->name('admin.alumni.edit');
        Route::put('/alumni/{id}', [AdminController::class, 'updateAlumni'])
            ->name('admin.alumni.update');
        Route::delete('/alumni/{id}', [AdminController::class, 'destroy'])
            ->name('admin.alumni.destroy');
        
        Route::post('/alumni/{id}/message', [AdminController::class, 'messageAlumni'])
            ->name('admin.alumni.message');

        Route::get('/settings', [AdminController::class, 'settings'])
            ->name('admin.settings');

        Route::post('/settings', [AdminController::class, 'store'])
            ->name('admin.settings.store');

        Route::put('/settings', [AdminController::class, 'updateProfile'])
            ->name('admin.settings.update');

        // Change Password (Authenticated)
        Route::put('/settings/password', [AdminController::class, 'changePassword'])->name('admin.password.update');

        // Events
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

        // Perks
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

        // Announcements
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

        Route::delete('/announcements/{announcement}/permanent-delete', [AnnouncementController::class, 'permanentDelete'])
            ->name('announcements.permanent-delete');

        // Alumni Tracer
        Route::get('/alumni_tracer', [TracerFormController::class, 'index'])
            ->name('admin.alumni_tracer');

        Route::get('/alumni_tracer/list', [TracerFormController::class, 'list'])
            ->name('admin.alumni_tracer.list');

        Route::get('/alumni_tracer/deleted', [TracerFormController::class, 'deleted'])
            ->name('admin.alumni_tracer.deleted');

        Route::get('/alumni_tracer/{id}', [TracerFormController::class, 'show'])
            ->name('admin.alumni_tracer.show');

        Route::post('/alumni_tracer', [TracerFormController::class, 'store'])
            ->name('admin.alumni_tracer.store');

        Route::put('/alumni_tracer/{id}', [TracerFormController::class, 'update'])
            ->name('admin.alumni_tracer.update');

        Route::delete('/alumni_tracer/{id}', [TracerFormController::class, 'destroy'])
            ->name('admin.alumni_tracer.destroy');

        Route::put('/alumni_tracer/{id}/restore', [TracerFormController::class, 'restore'])
            ->name('admin.alumni_tracer.restore');

        Route::post('/alumni_tracer/{id}/toggle-status', [TracerFormController::class, 'toggleStatus'])
            ->name('admin.alumni_tracer.toggle-status');

        // View Alumni Profile
        Route::get('/alumni/{id}/view', [AdminController::class, 'show'])
            ->name('admin.alumni.show');

        // Send Test Email
        Route::post('/alumni/{id}/send-test-email', [AdminController::class, 'sendTestEmail'])
            ->name('admin.alumni.send-test-email');

        
        // Messages
        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/conversations', [MessageController::class, 'getConversations'])->name('messages.conversations');
        Route::get('/messages/search/alumni', [MessageController::class, 'searchAlumni'])->name('messages.search');
        Route::get('/messages/{type}/{id}', [MessageController::class, 'getMessages'])->name('messages.get');
        Route::post('/messages/send', [MessageController::class, 'sendMessage'])->name('messages.send');

        // ✅ CORRECT - Don't include /admin/ since we're already in the prefix
        Route::post('/messages/decrypt', [MessageController::class, 'decryptMessage'])->name('messages.decrypt');

    });
});

// Public Route
Route::get('/', function () {
    return view('welcome');
});