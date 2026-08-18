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

    Route::get('/restricted', function () {
        return view('admin_restricted');
    })->name('admin.restricted');

    Route::get('/admin/debug-force-logout', [AdminController::class, 'debugForceLogout']);


    // Forgot Password Routes (Public)
    Route::get('/forgot-password', [AdminController::class, 'showForgotPassword'])->name('admin.forgot-password');
    Route::post('/forgot-password', [AdminController::class, 'sendResetLink'])->name('admin.send-reset-link');
    Route::get('/reset-password', [AdminController::class, 'showResetForm'])->name('admin.reset-password');
    Route::post('/reset-password', [AdminController::class, 'resetPassword'])->name('admin.reset-password.process');

    // 🔹 Protected Admin Routes
    Route::middleware('admin.auth')->group(function () {

        // Admin Management (Reset Password, Restrict, Delete)
        Route::post('/settings/admin/{id}/reset-password', [AdminController::class, 'resetAdminPassword'])
            ->name('admin.settings.reset-password');

        Route::patch('/settings/admin/{id}/toggle-restrict', [AdminController::class, 'toggleRestrictAdmin'])
            ->name('admin.settings.toggle-restrict');

        Route::delete('/settings/admin/{id}', [AdminController::class, 'deleteAdmin'])
            ->name('admin.settings.delete-admin');
        
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

        // ✅ FIXED: Badge count routes (NO duplicate /admin)
        Route::get('/alumni/archived-count', function() {
            return response()->json([
                'count' => \App\Models\Alumni::onlyTrashed()->count()
            ]);
        })->middleware('auth:admin');

        Route::get('/alumni/restricted-count', function() {
            return response()->json([
                'count' => \App\Models\Alumni::whereNull('deleted_at')->where('account_status', 0)->count()
            ]);
        })->middleware('auth:admin');

        // Alumni CRUD
        Route::get('/alumni/{id}/edit', [AdminController::class, 'editAlumni'])
            ->name('admin.alumni.edit');
        Route::put('/alumni/{id}', [AdminController::class, 'updateAlumni'])
            ->name('admin.alumni.update');
        Route::delete('/alumni/{id}', [AdminController::class, 'destroy'])
            ->name('admin.alumni.destroy');

        // Alumni Management Routes (Add these inside the admin.auth middleware group)
        Route::post('/alumni/{id}/archive', [AdminController::class, 'archiveAlumni'])
            ->name('admin.alumni.archive');

        Route::post('/alumni/{id}/restore', [AdminController::class, 'restoreAlumni'])
            ->name('admin.alumni.restore');

        Route::delete('/alumni/{id}/permanent-delete', [AdminController::class, 'permanentlyDeleteAlumni'])
            ->name('admin.alumni.permanent-delete');

        Route::get('/directory/archived', [AdminController::class, 'archivedAlumni'])
            ->name('admin.directory.archived');

        Route::get('/directory/restricted', [AdminController::class, 'restrictedAlumni'])
            ->name('admin.directory.restricted');

        // Get restriction reasons (optional, for API)
        Route::get('/alumni/restriction-reasons', [AdminController::class, 'getRestrictionReasons'])
            ->name('admin.alumni.restriction-reasons');

            // 🆕 Post Interactions Route - ADD THIS HERE
        Route::get('/posts/{post}/interactions', [AdminController::class, 'getPostInteractions'])
            ->name('admin.posts.interactions');

        // Alumni Management - Reset Password, Restrict, Export
        Route::post('/alumni/{id}/reset-password', [AdminController::class, 'resetAlumniPassword'])
            ->name('admin.alumni.reset-password');

        // Alumni Management - Reset Password, Restrict, Export
        Route::post('/alumni/{id}/reset-password', [AdminController::class, 'resetAlumniPassword'])
            ->name('admin.alumni.reset-password');

        Route::patch('/alumni/{id}/toggle-restrict', [AdminController::class, 'toggleRestrictAlumni'])
            ->name('admin.alumni.toggle-restrict');

        Route::get('/alumni/export', [AdminController::class, 'exportAlumni'])
            ->name('admin.alumni.export');
        
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

        //Permission Management routes
        Route::get('/settings/admin/{id}/permissions', [AdminController::class, 'showAdminPermissions']);
        Route::put('/settings/admin/{id}/permissions', [AdminController::class, 'updateAdminPermissions']);

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

        Route::delete('/events/{event}/permanent-delete', [EventController::class, 'permanentDelete'])
        ->name('events.permanent-delete');

        Route::get('/events/{event}/registrations', [EventController::class, 'registrations'])
        ->name('events.registrations');
        
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

        // Add this new route:
        Route::delete('/perks/{perk}/permanent-delete', [PerksController::class, 'permanentDelete'])
            ->name('perks.permanent-delete');

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

        Route::get('/alumni_tracer/phases', [TracerFormController::class, 'getPhasesDirectly'])
        ->name('admin.alumni_tracer.phases');

        // 🆕 Save phases directly (bypasses tracer_forms)
        Route::post('/alumni_tracer/phases/save', [TracerFormController::class, 'savePhasesDirectly'])
            ->name('admin.alumni_tracer.phases.save');

        // 🆕 Delete a single question directly
        Route::delete('/alumni_tracer/question/{questionId}', [TracerFormController::class, 'deleteQuestionDirectly'])
            ->name('admin.alumni_tracer.question.delete');

        // 🆕 Delete a single section directly
        Route::delete('/alumni_tracer/section/{sectionId}', [TracerFormController::class, 'deleteSectionDirectly'])
            ->name('admin.alumni_tracer.section.delete');

        // 🆕 Delete a single phase directly
        Route::delete('/alumni_tracer/phase/{phaseId}', [TracerFormController::class, 'deletePhaseDirectly'])
            ->name('admin.alumni_tracer.phase.delete');

        // 🆕 Get active form ID (for dashboard/analytics)
        Route::get('/alumni_tracer/active-form', [TracerFormController::class, 'getActiveFormId'])
            ->name('admin.alumni_tracer.active-form');

        // List routes (static paths - NO wildcards)
        Route::get('/alumni_tracer/list', [TracerFormController::class, 'list'])
            ->name('admin.alumni_tracer.list');

        Route::get('/alumni_tracer/deleted', [TracerFormController::class, 'deleted'])
            ->name('admin.alumni_tracer.deleted');

        // ⬇️ ALL specific sub-routes MUST come BEFORE the {id} wildcard ⬇️

        // Dashboard & Analytics routes
        Route::get('/alumni_tracer/{formId}/dashboard-stats', [TracerFormController::class, 'dashboardStats']);
        Route::get('/alumni_tracer/{formId}/recent-submissions', [TracerFormController::class, 'recentSubmissions']);
        Route::get('/alumni_tracer/{formId}/recent-activities', [TracerFormController::class, 'recentActivities']);
        Route::get('/alumni_tracer/question/{questionId}/analytics', [TracerFormController::class, 'questionAnalytics']);
        Route::get('/alumni_tracer/{formId}/analytics-kpis', [TracerFormController::class, 'analyticsKPIs']);

        // Reminder routes
        Route::get('/alumni_tracer/{formId}/incomplete-alumni', [TracerFormController::class, 'getIncompleteAlumni']);
        Route::post('/alumni_tracer/{formId}/send-reminder/{alumniId}', [TracerFormController::class, 'sendReminder']);
        Route::post('/alumni_tracer/{formId}/send-reminder-all', [TracerFormController::class, 'sendReminderToAll']);

        // 🆕 NEW: Get phases filtered by alumni type
        Route::get('/alumni_tracer/{formId}/phases-for-alumni/{alumniId}', [TracerFormController::class, 'getPhasesForAlumni']);

        // ⬇️ CRUD routes with {id} wildcard come LAST ⬇️
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

        Route::patch('/alumni_tracer/{id}/toggle-status', [TracerFormController::class, 'toggleStatus'])
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
        Route::post('/messages/decrypt', [MessageController::class, 'decryptMessage'])->name('messages.decrypt');
        Route::post('/messages/mark-read', [MessageController::class, 'markAsRead']);
        Route::post('/messages/send-with-attachments', [MessageController::class, 'sendWithAttachments']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/messages/attachments/{id}/url', [MessageController::class, 'getAttachmentUrl']);
        });

        // Get alumni info by ID (for chat redirect and new message)
        Route::get('/messages/{type}/{id}/info', [MessageController::class, 'getContactInfo'])
            ->where(['type' => 'alumni|admin'])
            ->name('messages.contact-info');

        Route::post('/messages/archive', [MessageController::class, 'archiveChat']);
        Route::post('/messages/mute', [MessageController::class, 'muteChat']);
        Route::post('/messages/delete', [MessageController::class, 'deleteChat']);
        Route::get('/messages/settings', [MessageController::class, 'getDmSettings']);

        // Preview bulk import file
        Route::post('/alumni/preview-bulk', [AdminController::class, 'previewBulkImport'])
            ->name('admin.alumni.preview-bulk');
            
        // Process bulk import with validated data
        Route::post('/alumni/process-bulk', [AdminController::class, 'processBulkImport'])
            ->name('admin.alumni.process-bulk');

        // ============================================
        // MODERATION & REPORT ROUTES (Add these)
        // ============================================

        // Moderation Routes
        Route::post('/moderate/post', [AdminDashboardController::class, 'moderatePost']);
        Route::post('/moderate/comment', [AdminDashboardController::class, 'moderateComment']);
        Route::post('/restrict-user', [AdminDashboardController::class, 'restrictUser']);
        Route::get('/posts/{id}', [AdminDashboardController::class, 'viewPost']);
        Route::get('/comments/{id}', [AdminDashboardController::class, 'viewComment']);
        Route::get('/reports/post/{id}', [AdminDashboardController::class, 'getPostReports']);
        Route::get('/reports/comment/{id}', [AdminDashboardController::class, 'getCommentReports']);
        Route::post('/moderate/bulk-posts', [AdminDashboardController::class, 'bulkModeratePosts']);
        Route::post('/moderate/bulk-comments', [AdminDashboardController::class, 'bulkModerateComments']);

        // 🆕 Get full post data for moderation modal (with images and comments)
        Route::get('/posts/{id}/full', [AdminController::class, 'getFullPost'])->name('admin.posts.full');

        // 🆕 Post Interactions (likes, comments, reposts)
        Route::get('/posts/{post}/interactions', [AdminController::class, 'getPostInteractions'])
            ->name('admin.posts.interactions');

        // View Post page (for moderation preview)
        Route::get('/posts/{id}/view', [AdminDashboardController::class, 'viewPostPage'])
            ->name('admin.posts.view');
    });
});

// Public Route
Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug-mail', function() {
    return [
        'mail_host' => config('mail.mailers.smtp.host'),
        'mail_port' => config('mail.mailers.smtp.port'),
        'mail_username' => config('mail.mailers.smtp.username'),
        'mail_password' => substr(config('mail.mailers.smtp.password'), 0, 10) . '...',
        'queue_connection' => config('queue.default'),
        'mail_timeout' => config('mail.mailers.smtp.timeout'),
    ];
});

