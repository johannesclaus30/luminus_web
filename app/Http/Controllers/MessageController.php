<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Alumni;
use App\Models\Admin;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display the messages page
     */
    public function index()
    {
        $admin = $this->getAuthenticatedAdmin();
        
        if (!$admin) {
            return redirect()->route('admin.login');
        }
        
        return view('admin_messages', compact('admin'));
    }

    /**
     * Get all conversations for the authenticated admin
     */
    public function getConversations(Request $request)
    {
        $adminId = $this->getAdminId();
        
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Get unique alumni who have conversations with this admin
        $conversations = Message::where(function($query) use ($adminId) {
                $query->where('sender_id', $adminId)
                      ->where('sender_type', 'admin')
                      ->where('receiver_type', 'alumni');
            })
            ->orWhere(function($query) use ($adminId) {
                $query->where('receiver_id', $adminId)
                      ->where('receiver_type', 'admin')
                      ->where('sender_type', 'alumni');
            })
            ->select('sender_id', 'receiver_id', 'sender_type', 'receiver_type')
            ->distinct()
            ->get();
        
        // Extract unique alumni IDs
        $alumniIds = [];
        foreach ($conversations as $conv) {
            if ($conv->sender_type === 'alumni') {
                $alumniIds[] = $conv->sender_id;
            } elseif ($conv->receiver_type === 'alumni') {
                $alumniIds[] = $conv->receiver_id;
            }
        }
        $alumniIds = array_unique($alumniIds);
        
        // Get alumni details
        $contacts = Alumni::whereIn('id', $alumniIds)
            ->where('verification_status', 'verified')
            ->get()
            ->map(function ($alumni) use ($adminId) {
                // Get last message
                $lastMessage = Message::where(function($query) use ($adminId, $alumni) {
                        $query->where('sender_id', $adminId)
                              ->where('sender_type', 'admin')
                              ->where('receiver_id', $alumni->id)
                              ->where('receiver_type', 'alumni');
                    })
                    ->orWhere(function($query) use ($adminId, $alumni) {
                        $query->where('sender_id', $alumni->id)
                              ->where('sender_type', 'alumni')
                              ->where('receiver_id', $adminId)
                              ->where('receiver_type', 'admin');
                    })
                    ->latest()
                    ->first();
                
                // Count unread messages
                $unreadCount = Message::where('sender_id', $alumni->id)
                    ->where('sender_type', 'alumni')
                    ->where('receiver_id', $adminId)
                    ->where('receiver_type', 'admin')
                    ->where('is_read', false)
                    ->count();
                
                return [
                    'id' => $alumni->id,
                    'full_name' => $alumni->full_name ?? $alumni->first_name . ' ' . $alumni->last_name,
                    'initials' => $alumni->initials ?? strtoupper(substr($alumni->first_name, 0, 1) . substr($alumni->last_name, 0, 1)),
                    'program' => $alumni->program,
                    'batch' => $alumni->year_graduated ? date('Y', strtotime($alumni->year_graduated)) : 'N/A',
                    'is_online' => $alumni->is_online ?? false,
                    'last_message' => $lastMessage ? $lastMessage->content : null,
                    'last_message_time' => $lastMessage ? $lastMessage->created_at->diffForHumans() : null,
                    'unread_count' => $unreadCount,
                    'avatar' => $alumni->alumni_photo,
                ];
            })
            ->sortByDesc(function ($contact) use ($adminId) {
                $latestMessage = Message::where(function($query) use ($adminId, $contact) {
                        $query->where('sender_id', $adminId)
                              ->where('receiver_id', $contact['id']);
                    })
                    ->orWhere(function($query) use ($adminId, $contact) {
                        $query->where('sender_id', $contact['id'])
                              ->where('receiver_id', $adminId);
                    })
                    ->max('created_at');
                return $latestMessage ? strtotime($latestMessage) : 0;
            })
            ->values();
        
        return response()->json($contacts);
    }

    /**
     * Get messages between admin and specific alumni
     */
    public function getMessages($alumniId, Request $request)
    {
        $adminId = $this->getAdminId();
        
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $messages = Message::where(function($query) use ($adminId, $alumniId) {
                $query->where('sender_id', $adminId)
                      ->where('sender_type', 'admin')
                      ->where('receiver_id', $alumniId)
                      ->where('receiver_type', 'alumni');
            })
            ->orWhere(function($query) use ($adminId, $alumniId) {
                $query->where('sender_id', $alumniId)
                      ->where('sender_type', 'alumni')
                      ->where('receiver_id', $adminId)
                      ->where('receiver_type', 'admin');
            })
            ->whereRaw('NOT (deleted_by @> ?::bigint[])', [json_encode([$adminId])])
            ->with('attachments')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'sender_type' => $message->sender_type,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at->toISOString(),
                    'time' => $message->created_at->format('g:i A'),
                    'attachments' => $message->attachments ?? [],
                ];
            });
        
        // Mark messages as read
        Message::where('sender_id', $alumniId)
            ->where('sender_type', 'alumni')
            ->where('receiver_id', $adminId)
            ->where('receiver_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return response()->json($messages);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:alumnis,id',
            'content' => 'required|string|max:5000',
        ]);
        
        $adminId = $this->getAdminId();
        
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $message = Message::create([
            'sender_id' => $adminId,
            'receiver_id' => $request->receiver_id,
            'sender_type' => 'admin',
            'receiver_type' => 'alumni',
            'content' => $request->content,
            'is_read' => false,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'content' => $message->content,
                'sender_type' => 'admin',
                'is_read' => false,
                'created_at' => $message->created_at->toISOString(),
                'time' => $message->created_at->format('g:i A'),
            ]
        ]);
    }

    /**
     * Search alumni
     */
    public function searchAlumni(Request $request)
    {
        $query = $request->get('q', '');
        
        $alumni = Alumni::where('verification_status', 'verified')
            ->where(function($q) use ($query) {
                $q->where('first_name', 'ilike', "%{$query}%")
                  ->orWhere('last_name', 'ilike', "%{$query}%")
                  ->orWhere('middle_name', 'ilike', "%{$query}%")
                  ->orWhere('student_id_number', 'ilike', "%{$query}%")
                  ->orWhere('program', 'ilike', "%{$query}%")
                  ->orWhere('email', 'ilike', "%{$query}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($alumni) {
                return [
                    'id' => $alumni->id,
                    'full_name' => $alumni->full_name ?? $alumni->first_name . ' ' . $alumni->last_name,
                    'initials' => $alumni->initials ?? strtoupper(substr($alumni->first_name, 0, 1) . substr($alumni->last_name, 0, 1)),
                    'program' => $alumni->program,
                    'batch' => $alumni->year_graduated ? date('Y', strtotime($alumni->year_graduated)) : 'N/A',
                    'is_online' => $alumni->is_online ?? false,
                    'avatar' => $alumni->alumni_photo,
                ];
            });
        
        return response()->json($alumni);
    }

    /**
     * Get the authenticated admin ID from session
     */
    private function getAdminId()
    {
        // Check session for admin data (most common for custom auth)
        if (session()->has('admin_id')) {
            return session('admin_id');
        }
        
        // Alternative: check if admin data is stored with a different key
        if (session()->has('admin_logged_in') && session()->has('admin_data')) {
            $adminData = session('admin_data');
            return $adminData['id'] ?? $adminData->id ?? null;
        }
        
        return null;
    }

    /**
     * Get the authenticated admin object from session
     */
    private function getAuthenticatedAdmin()
    {
        $adminId = $this->getAdminId();
        
        if ($adminId) {
            return Admin::find($adminId);
        }
        
        // Alternative: if full admin object is stored in session
        if (session()->has('admin_data')) {
            $adminData = session('admin_data');
            if (is_object($adminData)) {
                return $adminData;
            }
            if (is_array($adminData) && isset($adminData['id'])) {
                return Admin::find($adminData['id']);
            }
        }
        
        return null;
    }
}