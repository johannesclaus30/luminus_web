<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MessageController extends Controller
{
    public function typingStatus(Request $request, $contactId)
    {
        $userId = (int) $request->user()->id;
        $contactId = (int) $contactId;

        $typingUsers = [];
        $contact = Alumni::find($contactId);

        if ($contact && $this->isDirectTypingActive($userId, $contactId, $contactId)) {
            $typingUsers[] = $this->formatTypingUser($contact);
        }

        return response()->json([
            'is_typing' => !empty($typingUsers),
            'typing_users' => $typingUsers,
        ]);
    }

    public function setTypingStatus(Request $request, $contactId)
    {
        $validated = $request->validate([
            'is_typing' => ['sometimes', 'boolean'],
        ]);

        $userId = (int) $request->user()->id;
        $contactId = (int) $contactId;
        $isTyping = $validated['is_typing'] ?? true;
        $typingKey = $this->directTypingKey($userId, $contactId, $userId);

        if ($isTyping) {
            Cache::put($typingKey, true, now()->addSeconds(6));
        } else {
            Cache::forget($typingKey);
        }

        return response()->json([
            'status' => 'success',
            'is_typing' => $isTyping,
        ]);
    }

    public function unreadCount(Request $request)
    {
        $userId = $request->user()->id;

        $unreadCount = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }

    // 1. Fetch the entire chat history between the logged-in user and a specific contact
    public function fetchThread(Request $request, $contactId)
    {
        $userId = $request->user()->id; // The currently logged-in alumni

        $messages = Message::with('sender')
            ->where(function ($query) use ($userId, $contactId) {
                // Messages I sent to them
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $contactId);
            })
            ->orWhere(function ($query) use ($userId, $contactId) {
                // Messages they sent to me
                $query->where('sender_id', $contactId)
                      ->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc') // Oldest at the top, newest at the bottom
            ->get();

        return response()->json(['messages' => $messages]);
    }

    // 2. Save a new message to the database
    public function sendMessage(Request $request, $contactId)
    {
        // Validate that the user actually typed something
        $request->validate([
            'content' => 'required|string',
        ]);

        $userId = $request->user()->id;

        // Save it to your database
        $message = Message::create([
            'sender_id' => $userId,
            'receiver_id' => $contactId,
            'content' => $request->content,
            'is_read' => false, // Default to unread
        ]);

        // Load the sender info just in case the frontend needs the avatar/name
        $message->load('sender'); 

        // The absolute millisecond this saves, Supabase Realtime will automatically 
        // shoot it down the WebSocket tunnel to the other user!
        return response()->json($message, 201);
    }

    // 3. Mark messages as read when the user opens the chat screen
    public function markAsRead(Request $request, $contactId)
    {
        $userId = $request->user()->id;

        // Find all unread messages sent by the contact TO me, and mark them read
        Message::where('sender_id', $contactId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'success']);
    }

    private function directTypingKey(int $userId, int $contactId, int $typingUserId): string
    {
        $pair = [$userId, $contactId];
        sort($pair);

        return sprintf('chat_typing:direct:%d:%d:%d', $pair[0], $pair[1], $typingUserId);
    }

    private function isDirectTypingActive(int $userId, int $contactId, int $typingUserId): bool
    {
        return Cache::has($this->directTypingKey($userId, $contactId, $typingUserId));
    }

    private function formatTypingUser(Alumni $alumni): array
    {
        return [
            'id' => $alumni->id,
            'first_name' => $alumni->first_name,
            'last_name' => $alumni->last_name,
            'alumni_photo' => $alumni->alumni_photo,
        ];
    }
}