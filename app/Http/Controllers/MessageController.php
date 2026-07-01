<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Alumni;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    // 🚨 CRITICAL: This MUST match EXPO_PUBLIC_MESSAGE_SECRET in mobile app's .env
    // Check your mobile app folder: .env file -> EXPO_PUBLIC_MESSAGE_SECRET
    private $cryptoSecretKey = "LumiNUs_Chat_Sec_9x$2!kPqLmN8vWz";

    public function index()
    {
        $admin = $this->getAuthenticatedAdmin();
        
        if (!$admin) {
            return redirect()->route('admin.login');
        }
        
        return view('admin_messages', compact('admin'));
    }

    /**
     * Decrypt CryptoJS AES encrypted messages
     */
private function decryptMessageContent($content, $senderType, $receiverType)
{
    if (empty($content)) {
        return '';
    }
    
    $input = (string)$content;

    if (substr($input, 0, 4) !== 'enc:' && substr($input, 0, 10) !== 'U2FsdGVkX1') {
        return $input;
    }

    try {
        $payload = substr($input, 0, 4) === 'enc:' ? substr($input, 4) : $input;
        $decoded = base64_decode($payload, true);
        
        if ($decoded === false || substr($decoded, 0, 8) !== 'Salted__') {
            return $input;
        }

        $salt = substr($decoded, 8, 8);
        $ciphertext = substr($decoded, 16);

        // Try multiple key variations to handle special character interpretation issues
        $baseKey = $this->cryptoSecretKey;
        $possibleKeys = [
            $baseKey,                                    // Original
            str_replace('$', '\$', $baseKey),           // Escaped $
            str_replace('$2', '', $baseKey),            // Without $2
            str_replace('$', '', $baseKey),             // Without any $
            '$' . str_replace('$', '', $baseKey),       // $ at start only
        ];

        foreach ($possibleKeys as $index => $password) {
            Log::info("[DECRYPT] Trying key variant {$index}: '{$password}' (length: " . strlen($password) . ")");
            
            $derived = '';
            $block = '';
            
            while (strlen($derived) < 48) {
                $block = md5($block . $password . $salt, true);
                $derived .= $block;
            }
            
            $key = substr($derived, 0, 32);
            $iv = substr($derived, 32, 16);

            $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

            if ($decrypted !== false) {
                Log::info("[DECRYPT] ✅ SUCCESS with variant {$index}!");
                Log::info("[DECRYPT] Decrypted message: " . substr($decrypted, 0, 100));
                return $decrypted;
            }
        }

        Log::error('[DECRYPT] ❌ All key variants failed');
        Log::error('[DECRYPT] Original key: ' . $baseKey);
        Log::error('[DECRYPT] Original key hex: ' . bin2hex($baseKey));
        
        return $input;

    } catch (\Exception $e) {
        Log::error('[DECRYPT] Exception: ' . $e->getMessage());
        return $input;
    }
}

/**
 * Method 1: Standard EVP_BytesToKey with MD5 (CryptoJS default)
 */
private function deriveKeyMethod1($password, $salt)
{
    $derived = '';
    $block = '';
    
    while (strlen($derived) < 48) {
        $block = md5($block . $password . $salt, true);
        $derived .= $block;
    }
    
    return [
        'key' => substr($derived, 0, 32),
        'iv' => substr($derived, 32, 16),
    ];
}

/**
 * Method 2: EVP_BytesToKey without concatenating previous block
 */
private function deriveKeyMethod2($password, $salt)
{
    $d1 = md5($password . $salt, true);
    $d2 = md5($d1 . $password . $salt, true);
    $d3 = md5($d2 . $password . $salt, true);
    
    $derived = $d1 . $d2 . $d3;
    
    return [
        'key' => substr($derived, 0, 32),
        'iv' => substr($derived, 32, 16),
    ];
}

/**
 * Method 3: Try with password as UTF-8 bytes explicitly
 */
private function deriveKeyMethod3($password, $salt)
{
    $passwordBytes = mb_convert_encoding($password, 'UTF-8', 'UTF-8');
    $derived = '';
    $block = '';
    
    while (strlen($derived) < 48) {
        $block = md5($block . $passwordBytes . $salt, true);
        $derived .= $block;
    }
    
    return [
        'key' => substr($derived, 0, 32),
        'iv' => substr($derived, 32, 16),
    ];
}

    public function getConversations(Request $request)
    {
        try {
            $adminId = $this->getAdminId();
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            $sentContacts = Message::where('sender_id', $adminId)
                ->select('receiver_id as contact_id', 'receiver_type as contact_type')
                ->distinct()
                ->get();
                
            $receivedContacts = Message::where('receiver_id', $adminId)
                ->select('sender_id as contact_id', 'sender_type as contact_type')
                ->distinct()
                ->get();
            
            $allContacts = collect();
            $seenContacts = [];
            
            foreach ($sentContacts as $contact) {
                $contactType = is_string($contact->contact_type) ? $contact->contact_type : 'alumni';
                $key = $contactType . '_' . $contact->contact_id;
                if (!in_array($key, $seenContacts)) {
                    $seenContacts[] = $key;
                    $allContacts->push([
                        'contact_id' => $contact->contact_id,
                        'contact_type' => $contactType
                    ]);
                }
            }
            
            foreach ($receivedContacts as $contact) {
                $contactType = is_string($contact->contact_type) ? $contact->contact_type : 'alumni';
                $key = $contactType . '_' . $contact->contact_id;
                if (!in_array($key, $seenContacts)) {
                    $seenContacts[] = $key;
                    $allContacts->push([
                        'contact_id' => $contact->contact_id,
                        'contact_type' => $contactType
                    ]);
                }
            }
            
            $contacts = collect();
            
            foreach ($allContacts as $contact) {
                $contactId = $contact['contact_id'];
                $contactType = $contact['contact_type'];
                
                if ($contactType === 'admin' && $contactId == $adminId) {
                    continue;
                }
                
                $userData = null;
                
                if ($contactType === 'alumni') {
                    $userData = Alumni::find($contactId);
                } elseif ($contactType === 'admin') {
                    $userData = Admin::find($contactId);
                }
                
                if ($userData) {
                    $contactData = $this->buildContactData($userData, $contactType, $adminId);
                    if ($contactData) {
                        $contacts->push($contactData);
                    }
                }
            }
            
            $contacts = $contacts->sortByDesc(function ($contact) {
                return $contact['last_message_timestamp'] ?? 0;
            })->values();
            
            return response()->json($contacts);
            
        } catch (\Exception $e) {
            Log::error('Error in getConversations: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to load conversations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function buildContactData($user, $type, $adminId)
    {
        try {
            $type = is_string($type) ? $type : 'alumni';
            
            // 🔧 FIXED: Added sender_type and receiver_type to last message query
            $lastMessage = Message::where(function($query) use ($adminId, $user, $type) {
                    // Messages sent BY admin TO this user
                    $query->where('sender_id', $adminId)
                        ->where('sender_type', 'admin')
                        ->where('receiver_id', $user->id)
                        ->where('receiver_type', $type);
                })
                ->orWhere(function($query) use ($adminId, $user, $type) {
                    // Messages sent BY this user TO admin
                    $query->where('sender_id', $user->id)
                        ->where('sender_type', $type)
                        ->where('receiver_id', $adminId)
                        ->where('receiver_type', 'admin');
                })
                ->latest()
                ->first();
            
            // 🔧 FIXED: Added type check to unread count
            $unreadCount = Message::where('sender_id', $user->id)
                ->where('sender_type', $type)
                ->where('receiver_id', $adminId)
                ->where('receiver_type', 'admin')
                ->where('is_read', false)
                ->count();
            
            if ($type === 'admin') {
                $firstName = $user->admin_first_name ?? '';
                $lastName = $user->admin_last_name ?? '';
                $fullName = trim($firstName . ' ' . $lastName);
                $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                
                $photo = $user->photo ?? null;
                $program = 'Admin Staff';
                $batch = '-';
                $isOnline = true;
            } else {
                $firstName = $user->first_name ?? '';
                $lastName = $user->last_name ?? '';
                $fullName = trim($firstName . ' ' . $lastName);
                $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                
                $photo = $user->alumni_photo ?? null;
                $program = $user->program ?? 'N/A';
                $batch = $user->year_graduated ? date('Y', strtotime($user->year_graduated)) : 'N/A';
                $isOnline = $user->is_online ?? false;
            }
            
            // 🔧 FIX: Use proper URL resolution
            $avatar = null;
            if ($photo) {
                if ($type === 'admin') {
                    // Use the resolveAdminPhotoUrl method for admins
                    $avatar = $this->resolveAdminPhotoUrl($photo);
                } else {
                    // For alumni, check if it's a URL or use asset()
                    if (filter_var($photo, FILTER_VALIDATE_URL)) {
                        $avatar = $photo;
                    } else {
                        $avatar = asset('storage/' . ltrim($photo, '/'));
                    }
                }
            }
            
            $lastMessageContent = null;
            if ($lastMessage) {
                $lastMessageContent = $this->decryptMessageContent(
                    $lastMessage->content, 
                    $lastMessage->sender_type, 
                    $lastMessage->receiver_type
                );
            }
            
            $lastMessageTimestamp = $lastMessage ? strtotime($lastMessage->created_at) : 0;
            $lastMessageTime = null;
            if ($lastMessage && $lastMessage->created_at) {
                try {
                    $lastMessageTime = $lastMessage->created_at->diffForHumans();
                } catch (\Exception $e) {
                    $lastMessageTime = $lastMessage->created_at->format('M d, Y');
                }
            }
            
            return [
                'id' => (int)$user->id,
                'type' => $type,
                'full_name' => $fullName ?: 'Unknown',
                'initials' => $initials ?: '??',
                'program' => $program,
                'batch' => $batch,
                'is_online' => $isOnline,
                'last_message' => $lastMessageContent,
                'last_message_time' => $lastMessageTime,
                'last_message_timestamp' => $lastMessageTimestamp,
                'unread_count' => $unreadCount,
                'avatar' => $avatar,
            ];
        } catch (\Exception $e) {
            Log::error('Error building contact data: ' . $e->getMessage());
            return null;
        }
    }

    public function getMessages($type, $contactId, Request $request)
    {
        $adminId = $this->getAdminId();
        
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $type = is_string($type) ? $type : 'alumni';
        
        if (!in_array($type, ['alumni', 'admin'])) {
            return response()->json(['error' => 'Invalid contact type'], 400);
        }
        
        try {
            // 🔧 FIXED: Added sender_type and receiver_type to the query
            $messages = Message::where(function($query) use ($adminId, $contactId, $type) {
                    // Messages sent BY admin TO this contact
                    $query->where('sender_id', $adminId)
                        ->where('sender_type', 'admin')
                        ->where('receiver_id', $contactId)
                        ->where('receiver_type', $type);
                })
                ->orWhere(function($query) use ($adminId, $contactId, $type) {
                    // Messages sent BY this contact TO admin
                    $query->where('sender_id', $contactId)
                        ->where('sender_type', $type)
                        ->where('receiver_id', $adminId)
                        ->where('receiver_type', 'admin');
                })
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) use ($adminId) {
                    $decryptedContent = $this->decryptMessageContent(
                        $message->content,
                        $message->sender_type,
                        $message->receiver_type
                    );
                    
                    return [
                        'id' => $message->id,
                        'content' => $decryptedContent,
                        'sender_id' => $message->sender_id,
                        'sender_type' => $message->sender_type,
                        'receiver_id' => $message->receiver_id,
                        'receiver_type' => $message->receiver_type,
                        'is_read' => $message->is_read,
                        'is_outgoing' => $message->sender_id == $adminId,
                        'created_at' => $message->created_at ? $message->created_at->toISOString() : null,
                        'time' => $message->created_at ? $message->created_at->format('g:i A') : '',
                        'attachments' => [],
                    ];
                });
            
            // 🔧 FIXED: Mark messages as read with type check
            Message::where('sender_id', $contactId)
                ->where('sender_type', $type)
                ->where('receiver_id', $adminId)
                ->where('receiver_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);
            
            return response()->json($messages);
            
        } catch (\Exception $e) {
            Log::error('Error loading messages: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load messages'], 500);
        }
    }

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
                'content' => $request->content,
                'sender_id' => $adminId,
                'sender_type' => 'admin',
                'receiver_id' => $message->receiver_id,
                'receiver_type' => $message->receiver_type,
                'is_read' => false,
                'created_at' => $message->created_at->toISOString(),
                'time' => $message->created_at->format('g:i A'),
            ]
        ]);
    }

    public function searchAlumni(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }
            
            $currentAdminId = $this->getAdminId();
            
            // Log the search query for debugging
            Log::info('Search query: "' . $query . '"');
            
            // Search Alumni by first_name, last_name, student_id_number, or email
            $alumni = Alumni::where(function($q) use ($query) {
                    // Search by first name
                    $q->where('first_name', 'ILIKE', "%{$query}%")
                    // Search by last name
                    ->orWhere('last_name', 'ILIKE', "%{$query}%")
                    // Search by middle name
                    ->orWhere('middle_name', 'ILIKE', "%{$query}%")
                    // Search by student ID
                    ->orWhere('student_id_number', 'ILIKE', "%{$query}%")
                    // Search by email
                    ->orWhere('email', 'ILIKE', "%{$query}%")
                    // Search by program
                    ->orWhere('program', 'ILIKE', "%{$query}%");
                    
                    // Also search by full name (first_name + last_name)
                    $q->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", ["%{$query}%"]);
                    $q->orWhereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) ILIKE ?", ["%{$query}%"]);
                })
                ->limit(15) // Increased limit for better results
                ->get()
                ->map(function ($admin) {
                    $fullName = trim(
                        ($admin->admin_first_name ?? '') . ' ' . 
                        ($admin->admin_last_name ?? '')
                    );
                    $initials = strtoupper(
                        substr($admin->admin_first_name ?? 'A', 0, 1) . 
                        substr($admin->admin_last_name ?? 'A', 0, 1)
                    );
                    
                    // 🔧 FIX: Use resolveAdminPhotoUrl for admins
                    $avatar = $this->resolveAdminPhotoUrl($admin->photo ?? null);
                    
                    return [
                        'id' => $admin->id,
                        'type' => 'admin',
                        'full_name' => $fullName ?: 'Unknown Admin',
                        'initials' => $initials ?: 'AD',
                        'program' => 'Admin Staff',
                        'batch' => '-',
                        'student_id' => 'N/A',
                        'email' => $admin->admin_email ?? 'N/A',
                        'is_online' => true,
                        'avatar' => $avatar,
                    ];
                });
            
            // Log alumni search results
            Log::info('Alumni search results: ' . $alumni->count() . ' found');
            
            // Search Admins (excluding current admin)
            $admins = collect();
            
            if ($currentAdminId) {
                $admins = Admin::where('id', '!=', (int)$currentAdminId)
                    ->where(function($q) use ($query) {
                        $q->where('admin_first_name', 'ILIKE', "%{$query}%")
                        ->orWhere('admin_last_name', 'ILIKE', "%{$query}%")
                        ->orWhere('admin_email', 'ILIKE', "%{$query}%")
                        ->orWhere('admin_role', 'ILIKE', "%{$query}%");
                        
                        // Also search by full name
                        $q->orWhereRaw("CONCAT(admin_first_name, ' ', admin_last_name) ILIKE ?", ["%{$query}%"]);
                    })
                    ->limit(10)
                    ->get()
                    ->map(function ($admin) {
                        $fullName = trim(
                            ($admin->admin_first_name ?? '') . ' ' . 
                            ($admin->admin_last_name ?? '')
                        );
                        $initials = strtoupper(
                            substr($admin->admin_first_name ?? 'A', 0, 1) . 
                            substr($admin->admin_last_name ?? 'A', 0, 1)
                        );
                        
                        // Handle admin photo
                        $avatar = null;
                        $photo = $admin->photo ?? null;
                        if ($photo) {
                            if (filter_var($photo, FILTER_VALIDATE_URL)) {
                                $avatar = $photo;
                            } elseif (str_starts_with($photo, '/')) {
                                $avatar = $photo;
                            } else {
                                $avatar = asset('storage/' . ltrim($photo, '/'));
                            }
                        }
                        
                        return [
                            'id' => $admin->id,
                            'type' => 'admin',
                            'full_name' => $fullName ?: 'Unknown Admin',
                            'initials' => $initials ?: 'AD',
                            'program' => 'Admin Staff',
                            'batch' => '-',
                            'student_id' => 'N/A',
                            'email' => $admin->admin_email ?? 'N/A',
                            'is_online' => true,
                            'avatar' => $avatar,
                        ];
                    });
            }
            
            // Log admin search results
            Log::info('Admin search results: ' . $admins->count() . ' found');
            
            // Merge results (alumni first, then admins)
            $results = $alumni->merge($admins)->values();
            
            Log::info('Total search results: ' . $results->count());
            
            return response()->json($results);
            
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            Log::error('Search error trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function debugConversations(Request $request)
    {
        try {
            $adminId = $this->getAdminId();
            
            $sampleMessage = Message::where('sender_type', 'alumni')
                ->where('content', 'like', 'enc:%')
                ->first();
            
            $testData = [
                'admin_id' => $adminId,
                'secret_key_configured' => $this->cryptoSecretKey,
                'sample_original' => null,
                'sample_decrypted' => null,
                'decryption_test' => 'FAILED',
            ];
            
            if ($sampleMessage) {
                $testData['sample_original'] = $sampleMessage->content;
                $testData['sample_decrypted'] = $this->decryptMessageContent(
                    $sampleMessage->content,
                    $sampleMessage->sender_type,
                    $sampleMessage->receiver_type
                );
                
                $testData['decryption_test'] = (
                    $testData['sample_decrypted'] !== $sampleMessage->content && 
                    substr($testData['sample_decrypted'], 0, 4) !== 'enc:'
                ) ? 'SUCCESS ✅' : 'FAILED ❌';
            }
            
            return response()->json($testData);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    private function getAdminId()
    {
        if (session()->has('admin_id')) {
            return session('admin_id');
        }
        
        if (session()->has('admin_logged_in') && session()->has('admin_data')) {
            $adminData = session('admin_data');
            return $adminData['id'] ?? $adminData->id ?? null;
        }
        
        return null;
    }

    private function getAuthenticatedAdmin()
    {
        $adminId = $this->getAdminId();
        
        if ($adminId) {
            return Admin::find($adminId);
        }
        
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

    public function testEncryption(Request $request)
    {
        try {
            $testString = "Hello World Test";
            $password = $this->cryptoSecretKey;
            
            Log::info('[TEST] Starting encryption test');
            Log::info('[TEST] Password: ' . $password);
            Log::info('[TEST] Password length: ' . strlen($password));
            Log::info('[TEST] Password hex: ' . bin2hex($password));
            
            // Generate a random salt (8 bytes)
            $salt = openssl_random_pseudo_bytes(8);
            Log::info('[TEST] Salt (hex): ' . bin2hex($salt));
            
            // Derive key and IV using EVP_BytesToKey
            $derived = '';
            $block = '';
            
            while (strlen($derived) < 48) {
                $block = md5($block . $password . $salt, true);
                $derived .= $block;
            }
            
            $key = substr($derived, 0, 32);
            $iv = substr($derived, 32, 16);
            
            Log::info('[TEST] Derived Key (hex): ' . bin2hex($key));
            Log::info('[TEST] Derived IV (hex): ' . bin2hex($iv));
            
            // Encrypt
            $encrypted = openssl_encrypt(
                $testString,
                'aes-256-cbc',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            Log::info('[TEST] Encrypted length: ' . strlen($encrypted));
            
            // Format: "Salted__" + salt + encrypted
            $payload = 'Salted__' . $salt . $encrypted;
            $base64Payload = base64_encode($payload);
            $finalOutput = 'enc:' . $base64Payload;
            
            Log::info('[TEST] Final output: ' . $finalOutput);
            
            // Now try to decrypt it back
            $decrypted = $this->decryptMessageContent($finalOutput, 'alumni', 'admin');
            
            return response()->json([
                'success' => true,
                'test_string' => $testString,
                'password_used' => $password,
                'password_hex' => bin2hex($password),
                'salt_hex' => bin2hex($salt),
                'key_hex' => bin2hex($key),
                'iv_hex' => bin2hex($iv),
                'encrypted_output' => $finalOutput,
                'decrypted_result' => $decrypted,
                'match' => $decrypted === $testString ? 'YES ✅' : 'NO ❌'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
        
    }

    // Add this method to your MessageController
    public function decryptMessage(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'sender_type' => 'required|string',
            'receiver_type' => 'required|string',
        ]);
        
        $decrypted = $this->decryptMessageContent(
            $request->content,
            $request->sender_type,
            $request->receiver_type
        );
        
        return response()->json([
            'decrypted' => $decrypted
        ]);
    }

    /**
     * Get contact info by ID and type
     */
    public function getContactInfo($type, $id)
    {
        try {
            if (!in_array($type, ['alumni', 'admin'])) {
                return response()->json(['error' => 'Invalid contact type'], 400);
            }
            
            if ($type === 'alumni') {
                $user = Alumni::find($id);
            } else {
                $user = Admin::find($id);
            }
            
            if (!$user) {
                return response()->json(['error' => 'Contact not found'], 404);
            }
            
            if ($type === 'alumni') {
                $middleInitial = $user->middle_name 
                    ? ' ' . strtoupper(substr($user->middle_name, 0, 1)) . '. ' 
                    : ' ';
                $fullName = trim($user->first_name . $middleInitial . $user->last_name);
                $initials = strtoupper(
                    substr($user->first_name ?? 'A', 0, 1) . 
                    substr($user->last_name ?? 'A', 0, 1)
                );
                $batch = $user->year_graduated 
                    ? date('Y', strtotime($user->year_graduated)) 
                    : 'N/A';
                $program = $user->program ?? 'N/A';
                $isOnline = $user->is_online ?? false;
                $photo = $user->alumni_photo ?? $user->card_photo ?? null;
            } else {
                $fullName = trim(($user->admin_first_name ?? '') . ' ' . ($user->admin_last_name ?? ''));
                $initials = strtoupper(
                    substr($user->admin_first_name ?? 'A', 0, 1) . 
                    substr($user->admin_last_name ?? 'A', 0, 1)
                );
                $batch = '-';
                $program = 'Admin Staff';
                $isOnline = true;
                // 🔧 FIX: Use resolveAdminPhotoUrl for admins
                $photo = $user->photo ?? null;
            }
            
            // 🔧 FIX: Use proper URL resolution
            $avatar = null;
            if ($photo) {
                if ($type === 'admin') {
                    $avatar = $this->resolveAdminPhotoUrl($photo);
                } else {
                    if (filter_var($photo, FILTER_VALIDATE_URL)) {
                        $avatar = $photo;
                    } else {
                        $avatar = asset('storage/' . ltrim($photo, '/'));
                    }
                }
            }
            
            return response()->json([
                'id' => (int)$user->id,
                'type' => $type,
                'full_name' => $fullName ?: 'Unknown',
                'initials' => $initials ?: '??',
                'program' => $program,
                'batch' => $batch,
                'is_online' => $isOnline,
                'avatar' => $avatar,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching contact info: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch contact info'], 500);
        }
    }

    protected function resolveAdminPhotoUrl(?string $photoPath): ?string
    {
        $photoPath = trim((string) $photoPath);

        if ($photoPath === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $photoPath)) {
            return $photoPath;
        }

        if (str_starts_with($photoPath, '/storage/')) {
            return $photoPath;
        }

        if (str_starts_with($photoPath, 'storage/')) {
            return '/' . $photoPath;
        }

        if (str_starts_with($photoPath, '/')) {
            return $photoPath;
        }

        return Storage::disk('supabase_admin')->url($photoPath);
    }

}