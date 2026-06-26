<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Alumni;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema; 

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
        
        // Get all conversations (both alumni and admin)
        $conversations = Message::where(function($query) use ($adminId) {
                $query->where('sender_id', $adminId)
                    ->where('sender_type', 'admin');
                })
                ->orWhere(function($query) use ($adminId) {
                    $query->where('receiver_id', $adminId)
                        ->where('receiver_type', 'admin');
                })
                ->select('sender_id', 'receiver_id', 'sender_type', 'receiver_type')
                ->distinct()
                ->get();
        
        // Separate alumni and admin contacts
        $alumniIds = [];
        $adminIds = [];
        
        foreach ($conversations as $conv) {
            if ($conv->sender_type === 'alumni') {
                $alumniIds[] = $conv->sender_id;
            } elseif ($conv->receiver_type === 'alumni') {
                $alumniIds[] = $conv->receiver_id;
            } elseif ($conv->sender_type === 'admin' && $conv->sender_id != $adminId) {
                $adminIds[] = $conv->sender_id;
            } elseif ($conv->receiver_type === 'admin' && $conv->receiver_id != $adminId) {
                $adminIds[] = $conv->receiver_id;
            }
        }
        
        $alumniIds = array_unique($alumniIds);
        $adminIds = array_unique($adminIds);
        
        $contacts = collect();
        
        // Get alumni details
        if (!empty($alumniIds)) {
            $alumniContacts = Alumni::whereIn('id', $alumniIds)
                ->where('verification_status', 'verified')
                ->get()
                ->map(function ($alumni) use ($adminId) {
                    return $this->buildContactData($alumni, 'alumni', $adminId);
                });
            $contacts = $contacts->merge($alumniContacts);
        }
        
        // Get admin details
        if (!empty($adminIds)) {
            $adminContacts = Admin::whereIn('id', $adminIds)
                ->where('id', '!=', $adminId)
                ->get()
                ->map(function ($admin) use ($adminId) {
                    return $this->buildContactData($admin, 'admin', $adminId);
                });
            $contacts = $contacts->merge($adminContacts);
        }
        
        // Sort by latest message
        $contacts = $contacts->sortByDesc(function ($contact) use ($adminId) {
            $latestMessage = Message::where(function($query) use ($adminId, $contact) {
                    $query->where('sender_id', $adminId)
                        ->where('receiver_id', $contact['id'])
                        ->where('receiver_type', $contact['type']);
                })
                ->orWhere(function($query) use ($adminId, $contact) {
                    $query->where('sender_id', $contact['id'])
                        ->where('sender_type', $contact['type'])
                        ->where('receiver_id', $adminId)
                        ->where('receiver_type', 'admin');
                })
                ->max('created_at');
            return $latestMessage ? strtotime($latestMessage) : 0;
        })->values();
        
        return response()->json($contacts);
    }

private function buildContactData($user, $type, $adminId)
{
    // Get last message
    $lastMessage = Message::where(function($query) use ($adminId, $user, $type) {
            $query->where('sender_id', $adminId)
                ->where('sender_type', 'admin')
                ->where('receiver_id', $user->id)
                ->where('receiver_type', $type);
        })
        ->orWhere(function($query) use ($adminId, $user, $type) {
            $query->where('sender_id', $user->id)
                ->where('sender_type', $type)
                ->where('receiver_id', $adminId)
                ->where('receiver_type', 'admin');
        })
        ->latest()
        ->first();
    
    // Count unread messages
    $unreadCount = Message::where('sender_id', $user->id)
        ->where('sender_type', $type)
        ->where('receiver_id', $adminId)
        ->where('receiver_type', 'admin')
        ->where('is_read', false)
        ->count();
    
    if ($type === 'admin') {
        $fullName = trim($user->admin_first_name . ' ' . ($user->admin_middle_name ? $user->admin_middle_name . ' ' : '') . $user->admin_last_name);
        $initials = strtoupper(substr($user->admin_first_name, 0, 1) . substr($user->admin_last_name, 0, 1));
        
        // FIX: Build the correct photo URL for admin
        $avatar = null;
        if (!empty($user->photo)) {
            // Check if it's already a full URL
            if (filter_var($user->photo, FILTER_VALIDATE_URL)) {
                $avatar = $user->photo;
            } else {
                // Add storage path prefix
                $avatar = asset('storage/' . ltrim($user->photo, '/'));
            }
        }
        
        $program = 'Admin';
        $batch = '-';
    } else {
        $fullName = $user->first_name . ' ' . $user->last_name;
        $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
        
        // FIX: Build the correct photo URL for alumni
        $avatar = null;
        if (!empty($user->alumni_photo)) {
            // Check if it's already a full URL
            if (filter_var($user->alumni_photo, FILTER_VALIDATE_URL)) {
                $avatar = $user->alumni_photo;
            } else {
                // Add storage path prefix
                $avatar = asset('storage/' . ltrim($user->alumni_photo, '/'));
            }
        }
        
        $program = $user->program ?? '';
        $batch = $user->year_graduated ? date('Y', strtotime($user->year_graduated)) : 'N/A';
    }
    
    return [
        'id' => $user->id,
        'type' => $type,
        'full_name' => $fullName,
        'initials' => $initials,
        'program' => $program,
        'batch' => $batch,
        'is_online' => $type === 'admin' ? true : ($user->is_online ?? false),
        'last_message' => $lastMessage ? $lastMessage->content : null,
        'last_message_time' => $lastMessage ? $lastMessage->created_at->diffForHumans() : null,
        'unread_count' => $unreadCount,
        'avatar' => $avatar,
    ];
}

        /**
         * Get messages between admin and specific contact (alumni or admin)
         */
        public function getMessages($type, $contactId, Request $request)
        {
            $adminId = $this->getAdminId();
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            // Validate type
            if (!in_array($type, ['alumni', 'admin'])) {
                return response()->json(['error' => 'Invalid contact type'], 400);
            }
            
            try {
                $messages = Message::where(function($query) use ($adminId, $contactId, $type) {
                        $query->where('sender_id', $adminId)
                            ->where('sender_type', 'admin')
                            ->where('receiver_id', $contactId)
                            ->where('receiver_type', $type);
                    })
                    ->orWhere(function($query) use ($adminId, $contactId, $type) {
                        $query->where('sender_id', $contactId)
                            ->where('sender_type', $type)
                            ->where('receiver_id', $adminId)
                            ->where('receiver_type', 'admin');
                    })
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'content' => $message->content,
                            'sender_id' => $message->sender_id,  // ADD THIS LINE
                            'sender_type' => $message->sender_type,
                            'is_read' => $message->is_read,
                            'created_at' => $message->created_at->toISOString(),
                            'time' => $message->created_at->format('g:i A'),
                            'attachments' => [],
                        ];
                    });
                
                // Mark messages as read
                Message::where('sender_id', $contactId)
                    ->where('sender_type', $type)
                    ->where('receiver_id', $adminId)
                    ->where('receiver_type', 'admin')
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
                
                return response()->json($messages);
                
            } catch (\Exception $e) {
                \Log::error('Error loading messages: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to load messages', 'details' => $e->getMessage()], 500);
            }
        }

    /**
     * Send a message
     */
public function sendMessage(Request $request)
{
    $request->validate([
        'receiver_id' => 'required|integer',
        'receiver_type' => 'required|in:alumni,admin',
        'content' => 'required|string|max:5000',
    ]);
    
    $adminId = $this->getAdminId();
    
    if (!$adminId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    // Validate receiver exists based on type
    if ($request->receiver_type === 'alumni') {
        $exists = Alumni::where('id', $request->receiver_id)->exists();
    } else {
        $exists = Admin::where('id', $request->receiver_id)->exists();
    }
    
    if (!$exists) {
        return response()->json(['error' => 'Receiver not found'], 404);
    }
    
    $message = Message::create([
        'sender_id' => $adminId,
        'receiver_id' => $request->receiver_id,
        'sender_type' => 'admin',
        'receiver_type' => $request->receiver_type,
        'content' => $request->content,
        'is_read' => false,
    ]);
    
    return response()->json([
        'success' => true,
        'message' => [
            'id' => $message->id,
            'content' => $message->content,
            'sender_id' => $adminId,  // ADD THIS - explicitly include sender_id
            'sender_type' => 'admin',
            'is_read' => false,
            'created_at' => $message->created_at->toISOString(),
            'time' => $message->created_at->format('g:i A'),
        ]
    ]);
}

    /**
     * Search alumni and admins
     */
    public function searchAlumni(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }
            
            $results = [];
            $currentAdminId = $this->getAdminId();
            
            // Search Alumni
            $alumniQuery = Alumni::query();
            
            // Only add verification_status filter if the column exists
            if (Schema::hasColumn('alumnis', 'verification_status')) {
                $alumniQuery->where('verification_status', 'verified');
            }
            
            $alumni = $alumniQuery->where(function($q) use ($query) {
                    $q->where('first_name', 'LIKE', "%{$query}%")
                    ->orWhere('last_name', 'LIKE', "%{$query}%");
                    
                    if (Schema::hasColumn('alumnis', 'middle_name')) {
                        $q->orWhere('middle_name', 'LIKE', "%{$query}%");
                    }
                    if (Schema::hasColumn('alumnis', 'student_id_number')) {
                        $q->orWhere('student_id_number', 'LIKE', "%{$query}%");
                    }
                    if (Schema::hasColumn('alumnis', 'program')) {
                        $q->orWhere('program', 'LIKE', "%{$query}%");
                    }
                    if (Schema::hasColumn('alumnis', 'email')) {
                        $q->orWhere('email', 'LIKE', "%{$query}%");
                    }
                })
                ->limit(10)
                ->get()
                ->map(function ($alumni) {
                    $fullName = $alumni->first_name . ' ' . $alumni->last_name;
                    
                    $initials = strtoupper(substr($alumni->first_name, 0, 1) . substr($alumni->last_name, 0, 1));
                    
                    $batch = 'N/A';
                    if ($alumni->year_graduated) {
                        $batch = date('Y', strtotime($alumni->year_graduated));
                    }
                    
                    return [
                        'id' => $alumni->id,
                        'type' => 'alumni',
                        'full_name' => $fullName,
                        'initials' => $initials,
                        'program' => $alumni->program ?? 'N/A',
                        'batch' => $batch,
                        'is_online' => $alumni->is_online ?? false,
                        'avatar' => $alumni->alumni_photo ?? null,
                    ];
                });
            
            // Search Admins (exclude current admin)
            $admins = collect();
            
            if ($currentAdminId) {
                $admins = Admin::where('id', '!=', (int)$currentAdminId)
                    ->where(function($q) use ($query) {
                        $q->where('admin_first_name', 'LIKE', "%{$query}%")
                        ->orWhere('admin_last_name', 'LIKE', "%{$query}%")
                        ->orWhere('admin_email', 'LIKE', "%{$query}%");
                    })
                    ->limit(10)
                    ->get()
                    ->map(function ($admin) {
                        $fullName = trim($admin->admin_first_name . ' ' . ($admin->admin_middle_name ? $admin->admin_middle_name . ' ' : '') . $admin->admin_last_name);
                        
                        return [
                            'id' => $admin->id,
                            'type' => 'admin',
                            'full_name' => $fullName,
                            'initials' => strtoupper(substr($admin->admin_first_name, 0, 1) . substr($admin->admin_last_name, 0, 1)),
                            'program' => 'Admin',
                            'batch' => '-',
                            'is_online' => true,
                            'avatar' => $admin->photo ?? null,
                        ];
                    });
            }
            
            // Combine results
            $results = $alumni->merge($admins)->values();
            
            return response()->json($results);
            
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            \Log::error('Search error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ], 500);
        }
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