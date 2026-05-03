<?php

use App\Http\Controllers\API\AlumniProfileController;
use App\Http\Controllers\API\AlumniEmploymentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EventRegistrationController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\GroupChatController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\PerkController;
use App\Http\Controllers\API\TracerFormController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/perks', [PerkController::class, 'index']);
Route::get('/tracer-forms', [TracerFormController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/save-push-token', [AuthController::class, 'savePushToken']);
    Route::get('/alumni/profile', [AlumniProfileController::class, 'show']);
    Route::get('/alumni/profile/posts', [PostController::class, 'myPosts']);
    Route::get('/alumni/search', [AlumniProfileController::class, 'search']);
    Route::get('/alumni/{alumni}', [AlumniProfileController::class, 'view']);
    Route::get('/alumni/{alumni}/posts', [AlumniProfileController::class, 'posts']);
    Route::post('/alumni/{alumni}/follow', [AlumniProfileController::class, 'follow']);
    Route::delete('/alumni/{alumni}/follow', [AlumniProfileController::class, 'unfollow']);
    Route::post('/followers/{followRequestId}/accept', [AlumniProfileController::class, 'acceptFollowRequest']);
    Route::delete('/followers/{followRequestId}', [AlumniProfileController::class, 'declineFollowRequest']);
    Route::get('/contacts', [AlumniProfileController::class, 'contacts']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/messages/{contactId}', [MessageController::class, 'fetchThread']);
    Route::post('/messages/{contactId}', [MessageController::class, 'sendMessage']);
    Route::post('/messages/{contactId}/read', [MessageController::class, 'markAsRead']);
    Route::get('/messages/{contactId}/typing', [MessageController::class, 'typingStatus']);
    Route::post('/messages/{contactId}/typing', [MessageController::class, 'setTypingStatus']);
    Route::get('/group-chats', [GroupChatController::class, 'index']);
    Route::post('/group-chats', [GroupChatController::class, 'store']);
    Route::get('/group-chats/{groupChat}/messages', [GroupChatController::class, 'messages']);
    Route::post('/group-chats/{groupChat}/messages', [GroupChatController::class, 'sendMessage']);
    Route::post('/group-chats/{groupChat}/read', [GroupChatController::class, 'markAsRead']);
    Route::get('/group-chats/{groupChat}/typing', [GroupChatController::class, 'typingStatus']);
    Route::post('/group-chats/{groupChat}/typing', [GroupChatController::class, 'setTypingStatus']);
    Route::post('/group-chats/{groupChat}/messages/{message}/react', [GroupChatController::class, 'react']);
    Route::delete('/group-chats/{groupChat}/messages/{message}', [GroupChatController::class, 'destroy']);
    Route::put('/alumni/profile', [AlumniProfileController::class, 'update']);
    Route::post('/alumni/photo', [AlumniProfileController::class, 'uploadPhoto']);
    Route::post('/alumni/reset-password', [AuthController::class, 'resetAccountPassword']);
    Route::get('/notifications', [PostController::class, 'notifications']);
    Route::delete('/notifications/{notificationKey}', [PostController::class, 'dismissNotification']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::patch('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    Route::get('/event-registrations', [EventRegistrationController::class, 'index']);
    Route::post('/events/{event}/registrations', [EventRegistrationController::class, 'store']);
    Route::delete('/events/{event}/registrations', [EventRegistrationController::class, 'destroy']);
    Route::get('/posts/{post}/comments', [PostController::class, 'comments']);
    Route::post('/posts/{post}/reactions', [PostController::class, 'react']);
    Route::post('/posts/{post}/reposts', [PostController::class, 'repost']);
    Route::post('/posts/{post}/comments', [PostController::class, 'comment']);
    Route::post('/upload-photo', [AlumniProfileController::class, 'uploadProfilePhoto']);
    Route::post('/alumni/employments', [AlumniEmploymentController::class, 'store']);
    Route::patch('/alumni/employments/{employment}', [AlumniEmploymentController::class, 'update']);
    Route::delete('/alumni/employments/{employment}', [AlumniEmploymentController::class, 'destroy']);
    Route::post('/tracer-forms/{form}/submit', [TracerFormController::class, 'submit']);
    Route::get('/tracer-forms/{form}/user-response', [TracerFormController::class, 'userResponse']);

    // We will put things like creating posts, answering tracer studies,
    // and sending messages inside here later!
});