<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Alumni;
use App\Models\AdminPermission;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BrevoMailService; 

use App\Mail\AlumniWelcomeMail;
use App\Mail\TestAlumniEmail;
use App\Mail\AdminInvitationMail;
use App\Mail\AdminPasswordResetMail;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->session()->has('admin_id')) {
            return redirect('/admin/dashboard');
        }

        return view('admin_login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'admin_email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::query()
            ->where('admin_email', $credentials['admin_email'])
            ->first();

        if (! $admin) {
            throw ValidationException::withMessages([
                'admin_email' => 'Incorrect email or password.',
            ]);
        }

        $storedPassword = (string) ($admin->admin_password_hash ?? '');
        
        // 🔐 Check if password is hashed (for backward compatibility during transition)
        $isHashedPassword = password_get_info($storedPassword)['algo'] !== 0;

        // ✅ Supports both hashed AND legacy plain-text passwords
        $passwordMatches = $isHashedPassword 
            ? Hash::check($credentials['password'], $storedPassword)
            : $storedPassword === $credentials['password'];

        if (! $passwordMatches) {
            throw ValidationException::withMessages([
                'admin_email' => 'Incorrect email or password.',
            ]);
        }

        // Check if account is restricted
        if (($admin->account_status ?? 1) == 0) {
            return redirect()
                ->route('admin.restricted')
                ->with('restricted_email', $admin->admin_email);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'admin_id' => $admin->id,
            'admin_email' => $admin->admin_email,
            'admin_name' => trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
            'admin_role' => $admin->admin_role,
        ]);

        return redirect('/admin/dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch ONLY active alumni (not archived, not restricted)
        $alumni = Alumni::active()->latest('created_at')->get();

        // --- DIRECTORY STATS ---
        
        // 1. Total Alumni Count (active only)
        $totalAlumni = Alumni::active()->count();

        // 2. Recent Graduates (active alumni who graduated in the current year)
        $recentGraduates = Alumni::active()
            ->whereYear('year_graduated', now()->year)
            ->count();

        // 3. Unique Programs Offered (active alumni)
        $uniquePrograms = Alumni::active()
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->distinct('program')
            ->count('program');

        // 4. Alumni With Email Addresses (active alumni)
        $withEmails = Alumni::active()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();

        // Pass everything to the view
        return view('admin_directory', compact(
            'alumni', 
            'totalAlumni', 
            'recentGraduates', 
            'uniquePrograms', 
            'withEmails'
        ));
    }

    public function settings(Request $request)
    {
        $currentAdmin = $this->getAuthenticatedAdmin($request);
        $currentAdminPhotoUrl = $this->resolveAdminPhotoUrl($currentAdmin?->photo);
        $admins = Admin::query()->latest('created_at')->get();

        return view('admin_settings', compact('admins', 'currentAdmin', 'currentAdminPhotoUrl'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created admin and send invitation email.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_first_name' => ['required', 'string', 'max:255'],
            'admin_middle_name' => ['nullable', 'string', 'max:255'],
            'admin_last_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('admins', 'admin_email')],
            'phone_number' => ['required', 'string', 'max:50'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'admin_role' => ['required', Rule::in(['Executive Director', 'Academic Director', 'Coordinator', 'Assistant Coordinator'])],
        ]);

        // Generate a secure random temporary password
        $temporaryPassword = Str::random(12); // 12-character random string

        $admin = Admin::create([
            'admin_first_name' => $validated['admin_first_name'],
            'admin_middle_name' => $validated['admin_middle_name'] ?? null,
            'admin_last_name' => $validated['admin_last_name'],
            'admin_email' => $validated['admin_email'],
            'phone_number' => $validated['phone_number'],
            'photo' => null,
            'admin_password_hash' => Hash::make($temporaryPassword),
            'admin_role' => $validated['admin_role'],
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $admin->photo = $this->storeAdminPhoto($request, 'photo', $admin, null);
            $admin->save();
        }

        $admin->setupDefaultPermissions();

        // Send invitation email using Brevo API
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.admin_invitation', [
                'admin' => $admin,
                'temporaryPassword' => $temporaryPassword
            ])->render();
            
            $service->sendEmail(
                $admin->admin_email,
                'Welcome to LumiNUs - Your Admin Account',
                $htmlContent
            );
            
            return redirect()
                ->route('admin.settings', ['section' => 'add-admin'])
                ->with('status', 'Admin account created successfully! An invitation email has been sent to ' . $admin->admin_email . '.')
                ->with('temporary_password', $temporaryPassword);
                
        } catch (\Throwable $e) {
            \Log::error('Failed to send invitation email to ' . $admin->admin_email . ': ' . $e->getMessage());
            
            // Still return success but with a warning
            return redirect()
                ->route('admin.settings', ['section' => 'add-admin'])
                ->with('status', 'Admin account created, but the invitation email could not be sent. Please provide these credentials manually.')
                ->with('temporary_password', $temporaryPassword);
        }
    }

    public function updateProfile(Request $request)
    {
        $admin = $this->getAuthenticatedAdmin($request);

        if (! $admin) {
            abort(403);
        }

        $validated = $request->validate([
            'admin_first_name' => ['required', 'string', 'max:255'],
            'admin_middle_name' => ['nullable', 'string', 'max:255'],
            'admin_last_name'  => ['required', 'string', 'max:255'],
            'admin_email'      => ['required', 'email', 'max:255', Rule::unique('admins', 'admin_email')->ignore($admin->id)],
            'phone_number'     => ['required', 'string', 'max:50'],
            'photo'            => ['nullable', 'image', 'max:4096'],
            'remove_photo' => ['nullable', 'string'],
        ]);

        // Handle photo removal (priority over new upload)
        if ($request->has('remove_photo') && $request->input('remove_photo') == '1') {
            $this->deleteAdminPhoto($admin->photo);
            $admin->photo = null;
        } elseif ($request->hasFile('photo')) {
            $admin->photo = $this->storeAdminPhoto($request, 'photo', $admin, $admin->photo);
        }

        // Update other fields
        $admin->admin_first_name = $validated['admin_first_name'];
        $admin->admin_middle_name = $validated['admin_middle_name'] ?? null;
        $admin->admin_last_name  = $validated['admin_last_name'];
        $admin->admin_email      = $validated['admin_email'];
        $admin->phone_number     = $validated['phone_number'];
        $admin->save();

        // Update session data
        $request->session()->put([
            'admin_email' => $admin->admin_email,
            'admin_name'  => trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
        ]);

        return redirect()
            ->route('admin.settings', ['section' => 'account'])
            ->with('status', 'Account information updated successfully.');
    }

    public function storeAlumni(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'student_id_number' => ['required', 'string', 'max:255', Rule::unique('alumnis', 'student_id_number')],
            'email' => ['required', 'email', 'max:255', Rule::unique('alumnis', 'email')],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', 'string', 'max:50'],
            'year_graduated' => ['required', 'date'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'program' => ['nullable', 'string', 'max:255'],
            'card_photo' => ['nullable', 'image', 'max:4096'],
        ]);

        // Handle card photo upload to S3
        $cardPhotoPath = null;
        if ($request->hasFile('card_photo')) {
            $storedPath = $request->file('card_photo')->store('card_photo', 's3');
            if (! $storedPath) {
                throw ValidationException::withMessages([
                    'card_photo' => 'The card photo could not be uploaded to Supabase.',
                ]);
            }
            $cardPhotoPath = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($storedPath, '/');
        }

        // Generate a clean, mobile-friendly temporary password
        $temporaryPassword = $this->generateTemporaryPassword(10);

        // Create the alumni record
        $alumnus = Alumni::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'year_graduated' => $validated['year_graduated'],
            'student_id_number' => $validated['student_id_number'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'password_hash' => Hash::make($temporaryPassword),
            'verification_status' => 'verified',
            'needs_password_change' => true,
            'program' => $validated['program'] ?? null,
            'card_photo' => $cardPhotoPath,
        ]);

        // 🔑 CRITICAL: Create the Supabase Auth user
        $authCreated = $this->createSupabaseAuthUser(
            $alumnus->email,
            $temporaryPassword,
            $alumnus->first_name,
            $alumnus->last_name
        );

        if (!$authCreated) {
            \Log::error('Failed to create Supabase auth user for: ' . $alumnus->email);
        }

        // Send welcome email with the temporary password
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.welcome-alumni', [
                'alumnus' => $alumnus,
                'temporaryPassword' => $temporaryPassword,
            ])->render();
            
            $service->sendEmail(
                $alumnus->email,
                'Welcome to LumiNUs',
                $htmlContent
            );
        } catch (\Throwable $e) {
            \Log::error('Failed to send welcome email to ' . $alumnus->email . ': ' . $e->getMessage());
        }

        // If this is an AJAX request (from bulk import), return JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alumni account created successfully.',
                'alumnus' => [
                    'id' => $alumnus->id,
                    'name' => trim($alumnus->first_name . ' ' . $alumnus->last_name),
                    'email' => $alumnus->email,
                ]
            ], 201);
        }

        // Regular form submission redirect
        return redirect()
            ->route('admin.directory')
            ->with('status', 'Alumni account created successfully. Temporary password has been emailed.');
    }

    /**
     * Display the specified resource.
     */
  public function show(string $id)
    {
        $alumnus = Alumni::with([
            'addresses',
            'employments',
            'skills',
            'tracerResponses',
            'eventRegistrations.event',
            'followers',
            'following',
            'posts.images',
            'posts.comments',
            'posts.reactions'
        ])->findOrFail($id);
        
        // Process posts to add full image URLs
        $alumnus->posts->each(function($post) {
            $post->images->each(function($image) {
                $image->full_url = $this->getSupabaseStorageUrl($image->image_path);
            });
        });
        
        return view('directory.show', compact('alumnus'));
    }

    /**
     * Send a test email to the alumni.
     */
    public function sendTestEmail($id)
    {
        $alumnus = Alumni::findOrFail($id);

        if (empty($alumnus->email)) {
            return redirect()->back()->with('error', 'This alumni does not have an email address registered.');
        }

        try {
            // 1. Create the service
            $service = new BrevoMailService();
            
            // 2. Render your email template to HTML
            $htmlContent = view('emails.test-alumni', [
                'alumnus' => $alumnus
            ])->render();
            
            // 3. Send the email using Brevo's API
            $service->sendEmail(
                $alumnus->email,
                'Test Email from LumiNUs',
                $htmlContent
            );
            
            return redirect()->back()->with('success', "Test email successfully sent to {$alumnus->email}!");
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Mail error: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $alumnus = Alumni::findOrFail($id);

        try {
            // 1. Delete the alumni's photo from S3 to save storage space
            if ($alumnus->card_photo) {
                $this->deleteAlumniPhoto($alumnus->card_photo);
            }

            // 2. Delete the alumni record from the database
            $alumnus->delete();

            // 3. Return a JSON response since the frontend uses AJAX (fetch)
            return response()->json([
                'success' => true,
                'message' => 'Alumni account deleted successfully.'
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle foreign key constraint errors (e.g., if they have tracer forms or messages)
            // Error code 23503 is standard for foreign key violations in PostgreSQL/MySQL
            if ($e->getCode() === '23503' || str_contains($e->getMessage(), 'foreign key constraint')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this alumni because they have existing records (e.g., Tracer Forms). Please archive or remove those first.'
                ], 409); // 409 Conflict
            }

            // Fallback for other database errors
            \Log::error('Failed to delete alumni ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'A database error occurred while deleting the account.'
            ], 500);
            
        } catch (\Exception $e) {
            \Log::error('Failed to delete alumni ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    protected function getAuthenticatedAdmin(Request $request): ?Admin
    {
        $adminId = $request->session()->get('admin_id');

        if ($adminId && is_numeric($adminId)) {
            $admin = Admin::query()->where('id', (int) $adminId)->first();

            if ($admin) {
                return $admin;
            }
        }

        $adminEmail = $request->session()->get('admin_email');

        if ($adminEmail && is_string($adminEmail) && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return Admin::query()->where('admin_email', $adminEmail)->first();
        }

        return null;
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

    protected function storeAdminPhoto(Request $request, string $fieldName, Admin $admin, ?string $existingPhoto = null): string
    {
        $photo = $request->file($fieldName);

        if (! $photo) {
            return (string) $existingPhoto;
        }

        $this->deleteAdminPhoto($existingPhoto);

        $extension = strtolower($photo->getClientOriginalExtension() ?: $photo->extension() ?: 'jpg');
        $fileName = 'admin_' . $admin->id . '.' . $extension;
        Storage::disk('supabase_admin')->putFileAs('admin_photos', $photo, $fileName, 'public');

        return 'admin_photos/' . $fileName;
    }

    protected function deleteAdminPhoto(?string $photoPath): void
    {
        $normalizedPath = $this->normalizeAdminPhotoPath($photoPath);

        if (! $normalizedPath) {
            return;
        }

        $disk = Storage::disk('supabase_admin');

        if ($disk->exists($normalizedPath)) {
            $disk->delete($normalizedPath);
        }
    }

    protected function normalizeAdminPhotoPath(?string $photoPath): ?string
    {
        $photoPath = trim((string) $photoPath);

        if ($photoPath === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $photoPath)) {
            $parsedPath = parse_url($photoPath, PHP_URL_PATH) ?: '';
            $parsedPath = ltrim($parsedPath, '/');

            if (str_contains($parsedPath, 'admin_photos/')) {
                return substr($parsedPath, strpos($parsedPath, 'admin_photos/'));
            }

            if (str_contains($parsedPath, 'luminus_assets/')) {
                return substr($parsedPath, strpos($parsedPath, 'luminus_assets/') + strlen('luminus_assets/'));
            }

            return $parsedPath ?: null;
        }

        if (str_starts_with($photoPath, '/')) {
            $photoPath = ltrim($photoPath, '/');
        }

        if (str_starts_with($photoPath, 'luminus_assets/')) {
            return substr($photoPath, strlen('luminus_assets/'));
        }

        return $photoPath;
    }

    /**
     * Show the form for editing the specified alumni.
     */
    public function editAlumni(string $id)
    {
        $alumnus = Alumni::findOrFail($id);
        
        return view('directory.edit', compact('alumnus'));
    }

    /**
     * Update the specified alumni in storage.
     */
    public function updateAlumni(Request $request, string $id)
    {
        $alumnus = Alumni::findOrFail($id);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'student_id_number' => ['required', 'string', 'max:255', Rule::unique('alumnis', 'student_id_number')->ignore($alumnus->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('alumnis', 'email')->ignore($alumnus->id)],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', 'string', 'max:50'],
            'year_graduated' => ['required', 'date'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'program' => ['nullable', 'string', 'max:255'],
            'card_photo' => ['nullable', 'image', 'max:4096'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        // Handle photo removal
        if ($request->has('remove_photo') && $request->input('remove_photo')) {
            if ($alumnus->card_photo) {
                $this->deleteAlumniPhoto($alumnus->card_photo);
                $alumnus->card_photo = null;
            }
        } elseif ($request->hasFile('card_photo')) {
            // Delete old photo if exists
            if ($alumnus->card_photo) {
                $this->deleteAlumniPhoto($alumnus->card_photo);
            }
            
            // Upload new photo
            $storedPath = $request->file('card_photo')->store('card_photo', 's3');
            
            if ($storedPath) {
                $alumnus->card_photo = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($storedPath, '/');
            }
        }

        // Update alumni information
        $alumnus->update([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'student_id_number' => $validated['student_id_number'],
            'email' => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'year_graduated' => $validated['year_graduated'],
            'phone_number' => $validated['phone_number'] ?? null,
            'program' => $validated['program'] ?? null,
        ]);

        return redirect()
            ->route('admin.directory')
            ->with('status', 'Alumni information updated successfully.');
    }

    /**
     * Send message to alumni (placeholder)
     */
    public function messageAlumni(string $id)
    {
        $alumnus = Alumni::findOrFail($id);
        
        // For now, just redirect back with a message
        // You can implement actual messaging functionality here
        return redirect()
            ->route('admin.directory')
            ->with('status', 'Message feature coming soon for ' . $alumnus->first_name . ' ' . $alumnus->last_name);
    }

    protected function deleteAlumniPhoto(?string $photoPath): void
    {
        if (!$photoPath) {
            return;
        }
        
        // Extract the path from the full URL if it's a URL
        if (preg_match('/^https?:\/\//i', $photoPath)) {
            $parsedPath = parse_url($photoPath, PHP_URL_PATH) ?: '';
            $photoPath = ltrim($parsedPath, '/');
        }
        
        // Delete from S3
        if (Storage::disk('s3')->exists($photoPath)) {
            Storage::disk('s3')->delete($photoPath);
        }
    }

    /**
     * Generate a clean, mobile-app-friendly temporary password
     * Meets all mobile app password requirements:
     * - Minimum 6 characters
     * - At least 1 uppercase letter
     * - At least 1 number
     * - At least 1 special character
     * 
     * Uses only unambiguous characters for readability
     */
    protected function generateTemporaryPassword($length = 10): string
    {
        // Character sets (avoiding ambiguous characters: 0, O, o, 1, I, l)
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghijkmnopqrstuvwxyz';
        $numbers = '23456789';
        $special = '!@#$%^&*?';
        
        // Ensure we have at least one from each required set
        $password = [
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $special[random_int(0, strlen($special) - 1)],
        ];
        
        // All characters combined for remaining positions
        $all = $uppercase . $lowercase . $numbers . $special;
        $remainingLength = max(0, $length - 4);
        
        for ($i = 0; $i < $remainingLength; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }
        
        // Shuffle to randomize order
        shuffle($password);
        
        return implode('', $password);
    }

    /**
     * Create a new Supabase Auth user
     */
    protected function createSupabaseAuthUser($email, $password, $firstName = '', $lastName = '')
    {
        try {
            $supabaseKey = config('services.supabase.service_key');
            $supabaseUrl = config('services.supabase.url');
            
            if (!$supabaseKey || !$supabaseUrl) {
                \Log::error('Supabase credentials not configured');
                return false;
            }
            
            $response = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
            ])->post("{$supabaseUrl}/auth/v1/admin/users", [
                'email' => $email,
                'password' => $password,
                'email_confirm' => true,
                'user_metadata' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]
            ]);
            
            if ($response->failed()) {
                $errorData = $response->json();
                
                // If user already exists, try to update their password instead
                if (isset($errorData['error_code']) && $errorData['error_code'] === 'email_exists') {
                    \Log::info('User already exists in Supabase, attempting to update password: ' . $email);
                    return $this->updateSupabaseAuthPassword($email, $password);
                }
                
                \Log::error('Failed to create Supabase user: ' . $response->body());
                return false;
            }
            
            \Log::info('Supabase auth user created for: ' . $email);
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error creating Supabase auth user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update Supabase Auth user password
     */
    protected function updateSupabaseAuthPassword($email, $newPassword)
    {
        try {
            $supabaseKey = config('services.supabase.service_key');
            $supabaseUrl = config('services.supabase.url');
            
            if (!$supabaseKey || !$supabaseUrl) {
                \Log::error('Supabase credentials not configured');
                return false;
            }

            // First, get the user by email from Supabase Auth
            $userResponse = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
            ])->get("{$supabaseUrl}/auth/v1/admin/users", [
                'email' => $email
            ]);
            
            if ($userResponse->failed()) {
                \Log::error('Failed to fetch Supabase user: ' . $userResponse->body());
                return false;
            }
            
            $responseData = $userResponse->json();
            
            // 🔥 FIXED: Get the users array from the response
            $users = $responseData['users'] ?? [];
            
            // Check if user exists in Supabase Auth
            if (empty($users) || empty($users[0]['id'])) {
                \Log::warning('Supabase user not found for: ' . $email . ' - attempting to create');
                // User doesn't exist in Supabase Auth - create them
                return $this->createSupabaseAuthUser($email, $newPassword);
            }
            
            $userId = $users[0]['id'];
            
            // Update the user's password in Supabase Auth
            $updateResponse = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
            ])->put("{$supabaseUrl}/auth/v1/admin/users/{$userId}", [
                'password' => $newPassword
            ]);
            
            if ($updateResponse->failed()) {
                \Log::error('Failed to update Supabase password: ' . $updateResponse->body());
                return false;
            }
            
            \Log::info('Supabase auth password updated for: ' . $email);
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error updating Supabase auth password: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword()
    {
        return view('admin_forgot_password');
    }

    /**
     * Send a password reset link to the admin's email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'admin_email' => ['required', 'email', 'exists:admins,admin_email'],
        ], [
            'admin_email.exists' => 'No admin account found with this email address.',
        ]);

        $admin = Admin::where('admin_email', $request->admin_email)->first();

        // Generate a unique reset token
        $token = Str::random(64);
        
        // Store token in database with expiration (1 hour)
        $admin->update([
            'reset_token' => $token,
            'reset_token_expires_at' => now()->addHour(),
        ]);

        // Send reset email using Brevo API
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.admin_password_reset', [
                'admin' => $admin,
                'token' => $token
            ])->render();
            
            $service->sendEmail(
                $admin->admin_email,
                'Password Reset Request - LumiNUs',
                $htmlContent
            );
            
            return back()->with('status', 'Password reset link has been sent to your email address.');
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            
            // Still return success to prevent email enumeration
            return back()->with('status', 'Password reset link has been sent to your email address.');
        }
    }

    /**
     * Show the reset password form.
     */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');
        
        if (!$token || !$email) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'Invalid password reset link.');
        }

        // Verify token is valid
        $admin = Admin::where('admin_email', $email)
            ->where('reset_token', $token)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$admin) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'This password reset link is invalid or has expired.');
        }

        return view('admin_reset_password', compact('token', 'email'));
    }

    /**
     * Process the password reset.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $admin = Admin::where('admin_email', $request->email)
            ->where('reset_token', $request->token)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$admin) {
            return back()->with('error', 'This password reset link is invalid or has expired.');
        }

        // Update password and clear reset token
        $admin->update([
            'admin_password_hash' => Hash::make($request->password),
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        return redirect()->route('admin.login')
            ->with('status', 'Your password has been reset successfully. Please login with your new password.');
    }

    /**
     * Update the authenticated admin's password.
     */
    public function changePassword(Request $request)
    {
        $admin = $this->getAuthenticatedAdmin($request);

        if (!$admin) {
            abort(403);
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Verify current password
        $storedPassword = (string) ($admin->admin_password_hash ?? '');
        $isHashedPassword = password_get_info($storedPassword)['algo'] !== 0;
        
        $passwordMatches = $isHashedPassword 
            ? Hash::check($request->current_password, $storedPassword)
            : $storedPassword === $request->current_password;

        if (!$passwordMatches) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password you entered is incorrect.',
            ]);
        }

        // Update password
        $admin->update([
            'admin_password_hash' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('admin.settings', ['section' => 'security'])
            ->with('status', 'Your password has been changed successfully.');
    }

    /**
     * Reset an admin's password and send notification email.
     */
    public function resetAdminPassword(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $this->getAuthenticatedAdmin($request);

        if ($currentAdmin && $currentAdmin->id == $admin->id) {
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('status', 'To change your own password, use the Security tab.');
        }

        $temporaryPassword = Str::random(12);
        $admin->update([
            'admin_password_hash' => Hash::make($temporaryPassword),
        ]);

        // Send email notification
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.admin_password_reset_notify', [
                'admin' => $admin,
                'temporaryPassword' => $temporaryPassword,
                'resetBy' => trim($currentAdmin->admin_first_name . ' ' . $currentAdmin->admin_last_name)
            ])->render();
            
            $service->sendEmail(
                $admin->admin_email,
                'Your LumiNUs Admin Password Has Been Reset',
                $htmlContent
            );
            
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('status', 'Password for ' . $admin->admin_first_name . ' ' . $admin->admin_last_name . ' has been reset and emailed.')
                ->with('temporary_password', $temporaryPassword);
                
        } catch (\Throwable $e) {
            \Log::error('Failed to send password reset email to ' . $admin->admin_email . ': ' . $e->getMessage());
            
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('status', 'Password reset, but email could not be sent.')
                ->with('temporary_password', $temporaryPassword);
        }
    }

    public function toggleRestrictAdmin(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $this->getAuthenticatedAdmin($request);

        // Prevent self-restriction
        if ($currentAdmin && $currentAdmin->id == $admin->id) {
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('error', 'You cannot restrict your own account.');
        }

        // Check if account_status exists and has valid value
        $currentStatus = $admin->account_status ?? 1; // Default to active if null
        
        // Toggle the status
        $newStatus = $currentStatus == 1 ? 0 : 1;
        
        // Update the admin status
        $admin->update(['account_status' => $newStatus]);

        $isRestricted = $newStatus == 0;
        $action = $isRestricted ? 'restricted' : 'unrestricted';

        // ✅ FORCE LOGOUT: If restricting and the admin is currently logged in
        if ($isRestricted) {
            $this->forceLogoutAdmin($admin->id);
        }

        // Log the action for audit
        \Log::info('Admin account ' . $action, [
            'admin_id' => $admin->id,
            'admin_email' => $admin->admin_email,
            'performed_by' => $currentAdmin->id,
            'new_status' => $newStatus,
            'ip' => $request->ip(),
            'forced_logout' => $isRestricted
        ]);

        // Send email notification
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.admin_account_restricted', [
                'admin' => $admin,
                'isRestricted' => $isRestricted,
                'updatedBy' => trim($currentAdmin->admin_first_name . ' ' . $currentAdmin->admin_last_name)
            ])->render();
            
            $subject = $isRestricted 
                ? 'Your LumiNUs Admin Account Has Been Restricted' 
                : 'Your LumiNUs Admin Account Has Been Restored';
            
            $service->sendEmail($admin->admin_email, $subject, $htmlContent);
            
            $message = $isRestricted 
                ? 'Account for ' . $admin->admin_first_name . ' ' . $admin->admin_last_name . ' has been restricted and they have been logged out.'
                : 'Account for ' . $admin->admin_first_name . ' ' . $admin->admin_last_name . ' has been unrestricted.';
            
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('success', $message);
                
        } catch (\Throwable $e) {
            \Log::error('Failed to send restriction email to ' . $admin->admin_email . ': ' . $e->getMessage());
            
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('warning', 'Account for ' . $admin->admin_first_name . ' ' . $admin->admin_last_name . ' has been ' . $action . ', but email could not be sent.');
        }
    }

    /**
     * Delete an admin account and send notification email.
     */
    public function deleteAdmin(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $this->getAuthenticatedAdmin($request);

        if ($currentAdmin && $currentAdmin->id == $admin->id) {
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('status', 'You cannot delete your own account.');
        }

        $adminName = trim($admin->admin_first_name . ' ' . $admin->admin_last_name);
        $adminEmail = $admin->admin_email;
        $deletedBy = trim($currentAdmin->admin_first_name . ' ' . $currentAdmin->admin_last_name);

        // Delete photo if exists
        $this->deleteAdminPhoto($admin->photo);

        // Send email BEFORE deleting (so we still have the data)
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.admin_account_deleted', [
                'adminName' => $adminName,
                'adminEmail' => $adminEmail,
                'deletedBy' => $deletedBy
            ])->render();
            
            $service->sendEmail(
                $adminEmail,
                'Your LumiNUs Admin Account Has Been Removed',
                $htmlContent
            );
        } catch (\Throwable $e) {
            \Log::error('Failed to send deletion email to ' . $adminEmail . ': ' . $e->getMessage());
        }

        // Now delete the admin
        $admin->delete();

        return redirect()
            ->route('admin.settings', ['section' => 'roles'])
            ->with('status', 'Admin account for ' . $adminName . ' has been deleted and notified.');
    }

protected function forceLogoutAdmin($adminId)
{
    try {
        \Log::info('=== FORCE LOGOUT STARTED ===', [
            'admin_id' => $adminId,
            'session_driver' => config('session.driver'),
            'timestamp' => now()
        ]);
        
        if (config('session.driver') !== 'database') {
            \Log::error('Session driver is NOT database! Current: ' . config('session.driver'));
            return;
        }
        
        // Count sessions before
        $before = \DB::table('sessions')->count();
        \Log::info("Sessions before deletion: {$before}");
        
        // Delete by admin_id in payload
        $deleted = \DB::table('sessions')
            ->where('payload', 'LIKE', '%"admin_id";i:' . $adminId . '%')
            ->orWhere('payload', 'LIKE', '%"admin_id";s:' . $adminId . '%')
            ->orWhere('payload', 'LIKE', '%"admin_id":' . $adminId . '%')
            ->delete();
        
        // Count sessions after
        $after = \DB::table('sessions')->count();
        
        \Log::info('=== FORCE LOGOUT COMPLETED ===', [
            'admin_id' => $adminId,
            'sessions_deleted' => $deleted,
            'sessions_before' => $before,
            'sessions_after' => $after
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Force logout ERROR: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
    }
}

/**
 * Force logout an alumni by clearing their sessions
 */
protected function forceLogoutAlumni($alumniId)
{
    try {
        // Check if session driver is database
        if (config('session.driver') !== 'database') {
            \Log::info('Session driver is not database, skipping force logout for alumni: ' . $alumniId);
            return;
        }

        // Delete sessions where alumni_id is stored in the payload
        $deleted = \DB::table('sessions')
            ->where('payload', 'LIKE', '%"alumni_id";i:' . $alumniId . '%')
            ->orWhere('payload', 'LIKE', '%"alumni_id";s:' . $alumniId . '%')
            ->orWhere('payload', 'LIKE', '%"alumni_id":' . $alumniId . '%')
            ->orWhere('user_id', $alumniId) // For web sessions using user_id
            ->delete();

        \Log::info('Force logout alumni', [
            'alumni_id' => $alumniId,
            'sessions_deleted' => $deleted
        ]);

    } catch (\Exception $e) {
        \Log::error('Failed to force logout alumni ' . $alumniId . ': ' . $e->getMessage());
    }
}

    protected function forceLogoutAdminFallback($adminId)
    {
        try {
            $table = config('session.table', 'sessions');
            $sessions = \DB::table($table)->get(['id', 'payload']);
            $deletedCount = 0;
            
            foreach ($sessions as $session) {
                $payload = json_decode($session->payload ?? '{}', true);
                if (isset($payload['admin_id']) && (int)$payload['admin_id'] === (int)$adminId) {
                    \DB::table($table)->where('id', $session->id)->delete();
                    $deletedCount++;
                }
            }
            
            if ($deletedCount > 0) {
                \Log::info("Admin {$adminId} logged out forcefully (fallback). {$deletedCount} session(s) cleared.");
            }
            
        } catch (\Exception $e) {
            \Log::error("Failed to force logout admin {$adminId} (fallback): " . $e->getMessage());
        }
    }
    
    /**
     * Alternative: Clear sessions for a specific admin using Laravel's session handler
     */
    protected function clearAdminSessions($adminId)
    {
        // Get all session IDs (if using database)
        if (config('session.driver') === 'database') {
            $sessionIds = \DB::table('sessions')
                ->where('user_id', $adminId)
                ->pluck('id');
            
            foreach ($sessionIds as $sessionId) {
                // Delete the session from storage
                \DB::table('sessions')->where('id', $sessionId)->delete();
            }
        }
        
        // If using Redis or other drivers, you might need different approaches
        // For file-based sessions, you could delete session files
        if (config('session.driver') === 'file') {
            $sessionPath = config('session.files');
            $files = glob($sessionPath . '/*');
            
            foreach ($files as $file) {
                $content = file_get_contents($file);
                // Check if the session contains the admin ID
                if (strpos($content, '"user_id";i:' . $adminId) !== false) {
                    unlink($file);
                }
            }
        }
    }

    public function debugForceLogout(Request $request)
    {
        $adminId = $request->input('admin_id');
        
        if (!$adminId) {
            return response()->json(['error' => 'Please provide admin_id parameter']);
        }
        
        // 1. Check if admin exists
        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json(['error' => 'Admin not found']);
        }
        
        // 2. Get all sessions
        $sessions = \DB::table('sessions')->get();
        
        $result = [
            'admin_id' => $adminId,
            'admin_email' => $admin->admin_email,
            'session_driver' => config('session.driver'),
            'session_table' => config('session.table'),
            'total_sessions' => $sessions->count(),
            'matching_sessions' => [],
            'all_sessions' => []
        ];
        
        foreach ($sessions as $session) {
            $payload = json_decode($session->payload ?? '{}', true);
            
            $sessionInfo = [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'has_admin_id' => isset($payload['admin_id']),
                'admin_id_value' => $payload['admin_id'] ?? null,
                'has_user_id' => isset($payload['user_id']),
                'user_id_value' => $payload['user_id'] ?? null,
                'payload_keys' => array_keys($payload),
                'full_payload' => $payload
            ];
            
            $result['all_sessions'][] = $sessionInfo;
            
            // Check if this session matches the admin
            if (
                (isset($payload['admin_id']) && $payload['admin_id'] == $adminId) ||
                (isset($payload['user_id']) && $payload['user_id'] == $adminId) ||
                $session->user_id == $adminId ||
                str_contains($session->payload, '"admin_id";i:' . $adminId) ||
                str_contains($session->payload, '"admin_id";s:' . $adminId)
            ) {
                $result['matching_sessions'][] = $sessionInfo;
            }
        }
        
        // 3. Attempt to force logout
        $deleted = 0;
        foreach ($result['matching_sessions'] as $match) {
            \DB::table('sessions')->where('id', $match['id'])->delete();
            $deleted++;
        }
        
        $result['attempted_delete_count'] = $deleted;
        $result['remaining_sessions'] = \DB::table('sessions')->count();
        
        return response()->json($result);
    }

    /**
     * Show admin permissions for editing
     */
    public function showAdminPermissions(Request $request, $id)
    {
        $admin = Admin::with('permissions')->findOrFail($id);
        $currentAdmin = $this->getAuthenticatedAdmin($request);
        
        // Only Coordinator can manage permissions
        if (!$currentAdmin || $currentAdmin->admin_role !== 'Coordinator') {
            return response()->json(['error' => 'Unauthorized. Only Coordinators can manage permissions.'], 403);
        }
        
        // Cannot modify own permissions
        if ($currentAdmin->id == $admin->id) {
            return response()->json(['error' => 'You cannot modify your own permissions.'], 400);
        }
        
        $allModules = AdminPermission::getAvailableModules();
        $existingPermissions = $admin->permissions()->pluck('can_view', 'module')->toArray();
        
        $permissionsData = [];
        foreach ($allModules as $moduleKey => $moduleData) {
            if (array_key_exists($moduleKey, $existingPermissions)) {
                $canView = (bool) $existingPermissions[$moduleKey];
            } else {
                $defaults = AdminPermission::getDefaultPermissionsForRole($admin->admin_role);
                $canView = $defaults[$moduleKey] ?? false;
            }
            
            $permissionsData[$moduleKey] = [
                'name' => $moduleData['name'],
                'icon' => $moduleData['icon'],
                'description' => $moduleData['description'],
                'can_view' => $canView,
                'is_default' => !array_key_exists($moduleKey, $existingPermissions),
            ];
        }
        
        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'name' => trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
                'role' => $admin->admin_role,
            ],
            'permissions' => $permissionsData,
        ]);
    }

    /**
     * Update admin permissions
     */
    public function updateAdminPermissions(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $this->getAuthenticatedAdmin($request);
        
        // Only Coordinator can manage permissions
        if (!$currentAdmin || $currentAdmin->admin_role !== 'Coordinator') {
            return response()->json(['error' => 'Unauthorized. Only Coordinators can manage permissions.'], 403);
        }
        
        // Cannot modify own permissions
        if ($currentAdmin->id == $admin->id) {
            return response()->json(['error' => 'You cannot modify your own permissions.'], 400);
        }
        
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*.module' => 'required|string',
            'permissions.*.can_view' => 'required|boolean',
            'permissions.*.can_manage' => 'required|boolean',
        ]);
        
        $permissionsToSync = [];
        foreach ($validated['permissions'] as $perm) {
            $permissionsToSync[$perm['module']] = $perm['can_view'];
        }
        
        $admin->syncPermissions($permissionsToSync);
        
        \Log::info('Admin permissions updated', [
            'admin_id' => $admin->id,
            'admin_name' => trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
            'updated_by' => $currentAdmin->id,
            'permissions' => $validated['permissions'],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Permissions updated for ' . trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
        ]);
    }

    /**
     * Reset alumni password and trigger force change on next login
     */
    public function resetAlumniPassword(Request $request, $id)
    {
        $alumnus = Alumni::findOrFail($id);
        $currentAdmin = $this->getAuthenticatedAdmin($request);

        // Generate a clean, mobile-friendly temporary password
        $temporaryPassword = $this->generateTemporaryPassword(10);

        // 🔥 ADD THIS DEBUG LOG
        \Log::info('=== GENERATED PASSWORD ===', [
            'email' => $alumnus->email,
            'password' => $temporaryPassword,
            'length' => strlen($temporaryPassword),
            'has_uppercase' => preg_match('/[A-Z]/', $temporaryPassword) ? 'YES' : 'NO',
            'has_lowercase' => preg_match('/[a-z]/', $temporaryPassword) ? 'YES' : 'NO',
            'has_number' => preg_match('/[0-9]/', $temporaryPassword) ? 'YES' : 'NO',
            'has_special' => preg_match('/[!@#$%^&*?]/', $temporaryPassword) ? 'YES' : 'NO',
        ]);

        // Update password in your database
        $alumnus->update([
            'password_hash' => Hash::make($temporaryPassword),
            'needs_password_change' => true,
        ]);

        // Update Supabase Auth
        $authUpdated = $this->updateSupabaseAuthPassword(
            $alumnus->email,
            $temporaryPassword
        );

        // 🔥 ADD THIS DEBUG LOG
        \Log::info('=== SUPABASE UPDATE RESULT ===', [
            'email' => $alumnus->email,
            'auth_updated' => $authUpdated ? 'SUCCESS' : 'FAILED',
        ]);

        // ✅ ADD THIS DEBUG LOG
        \Log::info('=== PASSWORD RESET DEBUG ===', [
            'alumni_id' => $alumnus->id,
            'alumni_email' => $alumnus->email,
            'temporary_password' => $temporaryPassword,
            'auth_updated' => $authUpdated,
            'auth_updated_success' => $authUpdated ? 'YES' : 'NO',
            'timestamp' => now()->toDateTimeString()
        ]);

        if (!$authUpdated) {
            \Log::error('Failed to update Supabase auth password for: ' . $alumnus->email);
        }

        // Send email notification
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.alumni_password_reset_notify', [
                'alumnus' => $alumnus,
                'temporaryPassword' => $temporaryPassword,
                'resetBy' => trim(
                    ($currentAdmin->admin_first_name ?? '') . ' ' . 
                    ($currentAdmin->admin_last_name ?? '')
                )
            ])->render();

            $service->sendEmail(
                $alumnus->email,
                'Your LumiNUs Password Has Been Reset',
                $htmlContent
            );

            \Log::info('Alumni password reset', [
                'alumni_id' => $alumnus->id,
                'alumni_email' => $alumnus->email,
                'reset_by' => $currentAdmin->id ?? null,
                'ip' => $request->ip(),
                'auth_synced' => $authUpdated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. ' . $alumnus->first_name . ' has been emailed their new temporary password.'
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Failed to send password reset email to ' . $alumnus->email . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Password reset but email could not be sent. Please try again.'
            ], 500);
        }
    }

public function toggleRestrictAlumni(Request $request, $id)
{
    $alumnus = Alumni::findOrFail($id);
    $currentAdmin = $this->getAuthenticatedAdmin($request);

    $request->validate([
        'restrict' => 'required|boolean',
        'restriction_reason' => 'required_if:restrict,true|nullable|string|max:255',
        'restriction_comment' => 'nullable|string|max:1000',
    ]);

    $isRestricting = $request->input('restrict') == 1;
    $newStatus = $isRestricting ? 0 : 1;

    $updateData = [
        'account_status' => $newStatus,
    ];

    if ($isRestricting) {
        $updateData['restriction_reason'] = $request->input('restriction_reason');
        $updateData['restriction_comment'] = $request->input('restriction_comment', '');
        $updateData['restricted_by'] = $currentAdmin->id;
        $updateData['restricted_at'] = now();
    } else {
        // Clear restriction data when unrestricting
        $updateData['restriction_reason'] = null;
        $updateData['restriction_comment'] = null;
        $updateData['restricted_by'] = null;
        $updateData['restricted_at'] = null;
    }

    $alumnus->update($updateData);

    // Force logout if restricting
    if ($isRestricting) {
        $this->forceLogoutAlumni($alumnus->id);
    }

    // Send email notification
    try {
        $service = new BrevoMailService();
        $htmlContent = view('emails.alumni_account_restricted', [
            'alumnus' => $alumnus,
            'isRestricted' => $isRestricting,
            'updatedBy' => trim(
                ($currentAdmin->admin_first_name ?? '') . ' ' . 
                ($currentAdmin->admin_last_name ?? '')
            ),
            'restrictionReason' => $isRestricting ? $request->input('restriction_reason') : null,
            'restrictionComment' => $isRestricting ? $request->input('restriction_comment') : null,
        ])->render();

        $subject = $isRestricting 
            ? 'Your LumiNUs Account Has Been Restricted' 
            : 'Your LumiNUs Account Has Been Restored';

        $service->sendEmail($alumnus->email, $subject, $htmlContent);

        \Log::info('Alumni account ' . ($isRestricting ? 'restricted' : 'unrestricted'), [
            'alumni_id' => $alumnus->id,
            'alumni_email' => $alumnus->email,
            'performed_by' => $currentAdmin->id ?? null,
            'new_status' => $newStatus,
            'reason' => $request->input('restriction_reason'),
            'comment' => $request->input('restriction_comment'),
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => $isRestricting 
                ? $alumnus->first_name . '\'s account has been restricted and they have been logged out.'
                : $alumnus->first_name . '\'s account has been unrestricted.',
            'data' => [
                'account_status' => $newStatus,
                'restriction_reason' => $updateData['restriction_reason'] ?? null,
                'restriction_comment' => $updateData['restriction_comment'] ?? null,
            ]
        ], 200);

    } catch (\Throwable $e) {
        \Log::error('Failed to send restriction email to ' . $alumnus->email . ': ' . $e->getMessage());

        return response()->json([
            'success' => true,
            'message' => $isRestricting 
                ? 'Account restricted, but email could not be sent.'
                : 'Account unrestricted, but email could not be sent.',
            'warning' => true
        ], 200);
    }
}

/**
 * Archive an alumni account (soft delete)
 */
public function archiveAlumni(Request $request, $id)
{
    $alumnus = Alumni::findOrFail($id);
    $currentAdmin = $this->getAuthenticatedAdmin($request);

    // Check if already archived - use trashed() instead
    if ($alumnus->trashed()) {
        return response()->json([
            'success' => false,
            'message' => 'This account is already archived.'
        ], 400);
    }

    // Soft delete the account
    $alumnus->delete();

    // Force logout if they're logged in
    $this->forceLogoutAlumni($alumnus->id);

    \Log::info('Alumni account archived', [
        'alumni_id' => $alumnus->id,
        'alumni_email' => $alumnus->email,
        'performed_by' => $currentAdmin->id ?? null,
        'ip' => $request->ip()
    ]);

    // Send email notification
    try {
        $service = new BrevoMailService();
        $htmlContent = view('emails.alumni_account_archived', [
            'alumnus' => $alumnus,
            'archivedBy' => trim(
                ($currentAdmin->admin_first_name ?? '') . ' ' . 
                ($currentAdmin->admin_last_name ?? '')
            )
        ])->render();

        $service->sendEmail(
            $alumnus->email,
            'Your LumiNUs Account Has Been Archived',
            $htmlContent
        );

    } catch (\Throwable $e) {
        \Log::error('Failed to send archive email to ' . $alumnus->email . ': ' . $e->getMessage());
    }

    return response()->json([
        'success' => true,
        'message' => $alumnus->first_name . '\'s account has been archived successfully.'
    ], 200);
}

/**
 * Restore an archived alumni account
 */
public function restoreAlumni(Request $request, $id)
{
    $alumnus = Alumni::withTrashed()->findOrFail($id);
    $currentAdmin = $this->getAuthenticatedAdmin($request);

    // Check if not archived - use trashed() instead
    if (!$alumnus->trashed()) {
        return response()->json([
            'success' => false,
            'message' => 'This account is not archived.'
        ], 400);
    }

    // Restore the account
    $alumnus->restore();

    \Log::info('Alumni account restored from archive', [
        'alumni_id' => $alumnus->id,
        'alumni_email' => $alumnus->email,
        'performed_by' => $currentAdmin->id ?? null,
        'ip' => $request->ip()
    ]);

    return response()->json([
        'success' => true,
        'message' => $alumnus->first_name . '\'s account has been restored successfully.'
    ], 200);
}

/**
 * Permanently delete an alumni account (hard delete)
 */
public function permanentlyDeleteAlumni(Request $request, $id)
{
    $alumnus = Alumni::withTrashed()->findOrFail($id);
    $currentAdmin = $this->getAuthenticatedAdmin($request);

    try {
        // Delete all related records first
        
        // 1. Delete addresses
        $alumnus->addresses()->delete();
        
        // 2. Delete employments
        $alumnus->employments()->delete();
        
        // 3. Delete skills
        $alumnus->skills()->delete();
        
        // 4. Delete posts and their related data
        foreach ($alumnus->posts as $post) {
            // Delete post images
            $post->images()->delete();
            // Delete post reactions
            $post->reactions()->delete();
            // Delete post comments (they will be handled below, but let's be safe)
            $post->comments()->delete();
            // Delete post reports
            $post->reports()->delete();
            // Delete the post
            $post->delete();
        }
        
        // 5. Delete comments (any remaining comments not tied to posts)
        $alumnus->comments()->delete();
        
        // 6. Delete reactions (any remaining reactions)
        $alumnus->reactions()->delete();
        
        // 7. Delete reposts
        $alumnus->reposts()->delete();
        
        // 8. Delete event registrations
        $alumnus->eventRegistrations()->delete();
        
        // 9. Delete tracer responses and their answers
        foreach ($alumnus->tracerResponses as $response) {
            // Delete tracer answers
            $response->answers()->delete();
            // Delete the response
            $response->delete();
        }
        
        // 10. Delete messages (sent and received)
        $alumnus->messagesSent()->delete();
        $alumnus->messagesReceived()->delete();
        
        // 11. Delete group chat memberships
        $alumnus->groupChatMembers()->delete();
        
        // 12. Delete followers/following relationships
        $alumnus->followers()->delete();
        $alumnus->following()->delete();
        
        // 13. Delete dismissed notifications
        $alumnus->dismissedNotifications()->delete();
        
        // 14. Delete favorite chats
        $alumnus->favoriteChats()->delete();
        
        // 15. Delete DM settings
        $alumnus->dmSettings()->delete();
        
        // 16. Delete calls (made and received)
        $alumnus->callsMade()->delete();
        $alumnus->callsReceived()->delete();

        // 17. Delete photo from S3
        if ($alumnus->card_photo) {
            $this->deleteAlumniPhoto($alumnus->card_photo);
        }
        if ($alumnus->alumni_photo) {
            $this->deleteAlumniPhoto($alumnus->alumni_photo);
        }

        // Finally, permanently delete the alumni
        $alumnus->forceDelete();

        \Log::info('Alumni account and all related data permanently deleted', [
            'alumni_id' => $alumnus->id,
            'alumni_email' => $alumnus->email,
            'performed_by' => $currentAdmin->id ?? null,
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alumni account and all associated data have been permanently deleted.'
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Failed to permanently delete alumni ' . $id . ': ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while deleting the account: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Display archived alumni accounts
 */
public function archivedAlumni(Request $request)
{
    $archivedAlumni = Alumni::onlyTrashed()
        ->latest('deleted_at')
        ->paginate(20);

    $totalArchived = Alumni::onlyTrashed()->count();
    $totalRestricted = Alumni::whereNull('deleted_at')->where('account_status', 0)->count();
    
    // 👇 ADD THIS - Fetch restricted alumni for the restricted tab
    $restrictedAlumni = Alumni::whereNull('deleted_at')
        ->where('account_status', 0)
        ->latest('restricted_at')
        ->paginate(20);

    return view('directory.archived', compact(
        'archivedAlumni',
        'totalArchived',
        'totalRestricted',
        'restrictedAlumni'  // 👈 ADD THIS
    ));
}

/**
 * Display restricted alumni accounts
 */
public function restrictedAlumni(Request $request)
{
    return redirect('/admin/directory/archived#tab-restricted');
}

/**
 * Get restriction reasons for API
 */
public function getRestrictionReasons()
{
    return response()->json([
        'success' => true,
        'reasons' => Alumni::getRestrictionReasons()
    ]);
}

    /**
     * Export alumni data to CSV
     */
    public function exportAlumni(Request $request)
    {
        $alumni = Alumni::all();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="alumni_export_' . date('Y-m-d') . '.csv"',
        ];

        $columns = [
            'Student ID',
            'First Name',
            'Middle Name',
            'Last Name',
            'Email',
            'Phone Number',
            'Program',
            'Graduation Year',
            'Date of Birth',
            'Sex',
            'Verification Status',
            'Account Status',
            'Created At'
        ];

        $callback = function() use ($alumni, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($alumni as $alumnus) {
                fputcsv($file, [
                    $alumnus->student_id_number ?? '',
                    $alumnus->first_name ?? '',
                    $alumnus->middle_name ?? '',
                    $alumnus->last_name ?? '',
                    $alumnus->email ?? '',
                    $alumnus->phone_number ?? '',
                    $alumnus->program ?? '',
                    optional($alumnus->year_graduated)->format('Y') ?? '',
                    optional($alumnus->date_of_birth)->format('Y-m-d') ?? '',
                    $alumnus->sex ?? '',
                    $alumnus->verification_status ?? 'pending',
                    $alumnus->account_status == 1 ? 'Active' : 'Restricted',
                    optional($alumnus->created_at)->format('Y-m-d H:i:s') ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    /**
 * Preview bulk import data - Native PHP CSV parser
 * NO PACKAGES REQUIRED - works with your current PHP setup
 */
public function previewBulkImport(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,txt|max:5120', // 5MB max
    ]);

    try {
        $file = $request->file('file');
        $path = $file->getRealPath();
        
        // Read CSV using native PHP
        $handle = fopen($path, 'r');
        if (!$handle) {
            return response()->json([
                'success' => false,
                'message' => 'Could not read file.'
            ], 400);
        }
        
        // Auto-detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        
        $delimiter = ',';
        $delimiters = [',', ';', "\t", '|'];
        foreach ($delimiters as $d) {
            if (strpos($firstLine, $d) !== false) {
                $delimiter = $d;
                break;
            }
        }
        
        // Read all rows
        $records = [];
        while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            // Clean up the row
            $row = array_map(function($value) {
                return trim(str_replace(['\r', '\n', '\t'], '', $value));
            }, $row);
            $records[] = $row;
        }
        fclose($handle);
        
        if (empty($records) || count($records) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File is empty or contains only headers.'
            ], 400);
        }
        
        // Find header row
        $headerRowIndex = -1;
        $headers = [];
        $dataRows = [];
        
        for ($i = 0; $i < min(10, count($records)); $i++) {
            $row = $records[$i];
            if (empty(array_filter($row))) continue;
            
            $rowString = implode(' ', array_map('strtolower', array_map('trim', $row)));
            
            $hasStudentId = strpos($rowString, 'student id') !== false || 
                            strpos($rowString, 'studentid') !== false ||
                            strpos($rowString, 'id') !== false;
            $hasFirstName = strpos($rowString, 'first name') !== false || 
                            strpos($rowString, 'firstname') !== false ||
                            strpos($rowString, 'first') !== false;
            $hasLastName = strpos($rowString, 'last name') !== false || 
                            strpos($rowString, 'lastname') !== false ||
                            strpos($rowString, 'last') !== false;
            $hasEmail = strpos($rowString, 'email') !== false || 
                        strpos($rowString, 'e-mail') !== false;
            
            if ($hasStudentId && $hasFirstName && $hasLastName && $hasEmail) {
                $headerRowIndex = $i;
                $headers = array_map('trim', $row);
                break;
            }
        }
        
        if ($headerRowIndex === -1) {
            return response()->json([
                'success' => false,
                'message' => 'Could not find required headers. Please ensure your CSV has: Student ID, First Name, Last Name, and Email columns.'
            ], 400);
        }
        
        // Extract data rows
        for ($i = $headerRowIndex + 1; $i < count($records); $i++) {
            $row = $records[$i];
            if (empty(array_filter($row))) continue;
            
            // Ensure row has same number of columns as headers
            $rowData = [];
            foreach ($headers as $index => $header) {
                $value = isset($row[$index]) ? trim((string)$row[$index]) : '';
                $rowData[$header] = $value;
            }
            $dataRows[] = $rowData;
        }
        
        if (empty($dataRows)) {
            return response()->json([
                'success' => false,
                'message' => 'No data rows found after headers.'
            ], 400);
        }
        
        // Validate each row
        $validationResults = $this->validateBulkRows($dataRows);
        
        return response()->json([
            'success' => true,
            'headers' => $headers,
            'rows' => $dataRows,
            'total' => count($dataRows),
            'validation' => $validationResults,
            'delimiter' => $delimiter,
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Bulk import preview error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error reading file: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Validate bulk rows and return validation results
 */
protected function validateBulkRows($rows)
{
    $results = [];
    $requiredFields = ['student_id_number', 'first_name', 'last_name', 'email', 'program'];
    
    // Get all student IDs for duplicate check
    $studentIds = [];
    $emails = [];
    
    foreach ($rows as $index => $row) {
        $errors = [];
        $valid = true;
        
        // Helper to find value by key (case-insensitive)
        $findValue = function($searchKeys, $row) {
            foreach ($row as $key => $value) {
                $keyLower = strtolower(trim($key));
                foreach ((array)$searchKeys as $searchKey) {
                    if ($keyLower === strtolower(trim($searchKey))) {
                        return trim($value);
                    }
                }
            }
            return '';
        };
        
        // Get values with flexible key matching
        $studentId = $findValue(['Student ID', 'student_id', 'studentid', 'ID'], $row);
        $firstName = $findValue(['First Name', 'first_name', 'firstname', 'First'], $row);
        $lastName = $findValue(['Last Name', 'last_name', 'lastname', 'Last'], $row);
        $email = $findValue(['Email', 'e-mail', 'email_address'], $row);
        $program = $findValue(['Program', 'program', 'Strand', 'Department'], $row);
        $middleName = $findValue(['Middle Name', 'middle_name', 'middlename', 'Middle'], $row);
        $phone = $findValue(['Phone', 'phone_number', 'Mobile', 'Contact'], $row);
        $gradYear = $findValue(['Graduation Year', 'year_graduated', 'Year Graduated', 'Graduation'], $row);
        $dob = $findValue(['Date of Birth', 'date_of_birth', 'Birth Date', 'DOB'], $row);
        $sex = $findValue(['Sex', 'gender', 'Gender'], $row);
        
        // Check required fields
        if (empty($studentId)) {
            $errors[] = 'Student ID is required';
            $valid = false;
        }
        if (empty($firstName)) {
            $errors[] = 'First Name is required';
            $valid = false;
        }
        if (empty($lastName)) {
            $errors[] = 'Last Name is required';
            $valid = false;
        }
        if (empty($email)) {
            $errors[] = 'Email is required';
            $valid = false;
        }
        if (empty($program)) {
            $errors[] = 'Program is required';
            $valid = false;
        }
        
        // Validate email format
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
            $valid = false;
        }
        
        // Check for duplicate student IDs in the import
        if (!empty($studentId)) {
            $studentIds[] = $studentId;
        }
        if (!empty($email)) {
            $emails[] = $email;
        }
        
        // Store the row data with normalized keys for later use
        $results[$index] = [
            'valid' => $valid,
            'errors' => $errors,
            'data' => [
                'student_id_number' => $studentId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'program' => $program,
                'middle_name' => $middleName,
                'phone_number' => $phone,
                'year_graduated' => $gradYear,
                'date_of_birth' => $dob,
                'sex' => $sex,
            ]
        ];
    }
    
    // Check for duplicates within the import
    $duplicateStudentIds = array_filter(array_count_values($studentIds), function($count) {
        return $count > 1;
    });
    
    $duplicateEmails = array_filter(array_count_values($emails), function($count) {
        return $count > 1;
    });
    
    foreach ($results as $index => &$result) {
        $studentId = $result['data']['student_id_number'] ?? '';
        $email = $result['data']['email'] ?? '';
        
        if (!empty($studentId) && isset($duplicateStudentIds[$studentId]) && $duplicateStudentIds[$studentId] > 1) {
            $result['errors'][] = 'Duplicate Student ID in import file';
            $result['valid'] = false;
        }
        
        if (!empty($email) && isset($duplicateEmails[$email]) && $duplicateEmails[$email] > 1) {
            $result['errors'][] = 'Duplicate Email in import file';
            $result['valid'] = false;
        }
    }
    
    return $results;
}

/**
 * Process bulk import with validated data
 */
public function processBulkImport(Request $request)
{
    $request->validate([
        'data' => 'required|array',
        'data.*' => 'required|array',
        'mapping' => 'required|array',
    ]);

    $importData = $request->input('data');
    $mapping = $request->input('mapping');
    
    $results = [
        'successful' => 0,
        'failed' => 0,
        'errors' => [],
        'duplicates' => [],
        'warnings' => [],
    ];
    
    // Get existing student IDs and emails for duplicate checking
    $existingStudentIds = Alumni::pluck('student_id_number')->toArray();
    $existingEmails = Alumni::pluck('email')->toArray();
    
    foreach ($importData as $index => $row) {
        try {
            // Map fields according to user's mapping
            $record = [];
            foreach ($mapping as $dbField => $csvColumn) {
                if (!empty($csvColumn) && isset($row[$csvColumn])) {
                    $record[$dbField] = trim((string)$row[$csvColumn]);
                }
            }
            
            // Check for duplicates in database
            if (in_array($record['student_id_number'] ?? '', $existingStudentIds)) {
                $results['duplicates'][] = "Student ID {$record['student_id_number']} already exists (row " . ($index + 1) . ")";
                $results['failed']++;
                continue;
            }
            
            if (in_array($record['email'] ?? '', $existingEmails)) {
                $results['duplicates'][] = "Email {$record['email']} already exists (row " . ($index + 1) . ")";
                $results['failed']++;
                continue;
            }
            
            // Validate required fields
            if (empty($record['first_name']) || empty($record['last_name']) || 
                empty($record['student_id_number']) || empty($record['email']) || empty($record['program'])) {
                $results['errors'][] = "Missing required fields in row " . ($index + 1);
                $results['failed']++;
                continue;
            }
            
            // Generate temporary password
            $temporaryPassword = $this->generateTemporaryPassword(10);
            
            // Format dates properly
            $yearGraduated = null;
            if (!empty($record['year_graduated'])) {
                // Try to parse various date formats
                $yearGraduated = $this->parseDate($record['year_graduated']);
            }
            
            $dateOfBirth = null;
            if (!empty($record['date_of_birth'])) {
                $dateOfBirth = $this->parseDate($record['date_of_birth']);
            }
            
            // Create alumni record
            $alumnus = Alumni::create([
                'first_name' => $record['first_name'],
                'middle_name' => $record['middle_name'] ?? null,
                'last_name' => $record['last_name'],
                'student_id_number' => $record['student_id_number'],
                'email' => $record['email'],
                'phone_number' => $record['phone_number'] ?? null,
                'program' => $record['program'] ?? null,
                'date_of_birth' => $dateOfBirth,
                'sex' => $record['sex'] ?? null,
                'year_graduated' => $yearGraduated,
                'password_hash' => Hash::make($temporaryPassword),
                'verification_status' => 'verified',
                'needs_password_change' => true,
                'account_status' => 1,
            ]);
            
            // Create Supabase auth user
            $authCreated = $this->createSupabaseAuthUser(
                $alumnus->email,
                $temporaryPassword,
                $alumnus->first_name,
                $alumnus->last_name
            );
            
            if (!$authCreated) {
                $results['warnings'][] = "Supabase user creation failed for {$alumnus->email} but local record was created.";
            }
            
            // Send welcome email
            try {
                $service = new BrevoMailService();
                $htmlContent = view('emails.welcome-alumni', [
                    'alumnus' => $alumnus,
                    'temporaryPassword' => $temporaryPassword,
                ])->render();
                
                $service->sendEmail(
                    $alumnus->email,
                    'Welcome to LumiNUs',
                    $htmlContent
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome email to ' . $alumnus->email . ': ' . $e->getMessage());
                $results['warnings'][] = "Email could not be sent to {$alumnus->email}";
            }
            
            // Add to existing lists to prevent duplicates within the same import
            $existingStudentIds[] = $record['student_id_number'];
            $existingEmails[] = $record['email'];
            $results['successful']++;
            
        } catch (\Exception $e) {
            $results['errors'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
            $results['failed']++;
            \Log::error('Bulk import error at row ' . ($index + 1) . ': ' . $e->getMessage());
        }
    }
    
    return response()->json([
        'success' => true,
        'results' => $results,
        'message' => "Import complete! {$results['successful']} successful, {$results['failed']} failed.",
    ]);
}

/**
 * Helper function to parse various date formats
 */
protected function parseDate($dateString)
{
    if (empty($dateString)) {
        return null;
    }
    
    $dateString = trim($dateString);
    
    // Try to parse common date formats
    $formats = [
        'Y-m-d',
        'm/d/Y',
        'd/m/Y',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'm-d-Y',
        'd-m-Y',
        'Y.m.d',
        'm.d.Y',
        'd.m.Y',
    ];
    
    foreach ($formats as $format) {
        $date = \DateTime::createFromFormat($format, $dateString);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
    }
    
    // Try to parse just the year
    if (preg_match('/^\d{4}$/', $dateString)) {
        return $dateString . '-01-01';
    }
    
    // Try to parse with strtotime
    $timestamp = strtotime($dateString);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }
    
    // Return as is if no format matches
    return $dateString;
}

/**
 * Get the full Supabase storage URL for a given path
 */
protected function getSupabaseStorageUrl($path)
{
    if (empty($path)) {
        return null;
    }
    
    // If it's already a full URL, return as is
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    
    // Remove leading slashes for consistency
    $path = ltrim($path, '/');
    
    // Get Supabase URL from config
    $supabaseUrl = config('filesystems.disks.s3.url', '');
    
    if (empty($supabaseUrl)) {
        // Fallback: construct from env
        $supabaseUrl = rtrim(config('services.supabase.url', ''), '/') . '/storage/v1/object/public/luminus_assets/';
    } else {
        // Use the S3 URL (which should point to your Supabase bucket)
        $supabaseUrl = rtrim($supabaseUrl, '/') . '/';
    }
    
    return $supabaseUrl . $path;
}

public function getPostInteractions(Request $request, $postId)
{
    try {
        $post = \App\Models\Post::with([
            'reactions.alumni',
            'comments.alumni',
            'reposts.alumni'
        ])->findOrFail($postId);
        
        $type = $request->input('type', 'likes');
        $data = [];
        $total = 0;
        
        // Helper function to get profile photo URL
        $getProfilePhotoUrl = function($alumnus) {
            if (!$alumnus) return null;
            
            $photoPath = trim((string) ($alumnus->alumni_photo ?: $alumnus->card_photo));
            if (empty($photoPath)) return null;
            
            if (preg_match('/^https?:\/\//i', $photoPath)) {
                return $photoPath;
            } elseif (str_starts_with($photoPath, '/storage/')) {
                return $photoPath;
            } elseif (str_starts_with($photoPath, 'storage/')) {
                return '/' . $photoPath;
            } elseif (str_starts_with($photoPath, '/')) {
                return $photoPath;
            } elseif (trim((string) config('filesystems.disks.s3.url')) !== '') {
                return rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($photoPath, '/');
            } else {
                return asset('storage/' . ltrim($photoPath, '/'));
            }
        };
        
        switch ($type) {
            case 'likes':
                $data = $post->reactions
                    ->sortByDesc('created_at')
                    ->map(function($reaction) use ($getProfilePhotoUrl) {
                        return [
                            'id' => $reaction->alumni->id ?? null,
                            'first_name' => $reaction->alumni->first_name ?? 'Unknown',
                            'last_name' => $reaction->alumni->last_name ?? '',
                            'profile_photo' => $getProfilePhotoUrl($reaction->alumni),
                            'created_at' => $reaction->created_at ? $reaction->created_at->toISOString() : null,
                        ];
                    })
                    ->values()
                    ->toArray();
                $total = $post->reactions->count();
                break;
                
            case 'comments':
                $data = $post->comments
                    ->sortByDesc('created_at')
                    ->map(function($comment) use ($getProfilePhotoUrl) {
                        return [
                            'id' => $comment->alumni->id ?? null,
                            'first_name' => $comment->alumni->first_name ?? 'Unknown',
                            'last_name' => $comment->alumni->last_name ?? '',
                            'profile_photo' => $getProfilePhotoUrl($comment->alumni),
                            'comment' => $comment->comment,
                            'created_at' => $comment->created_at ? $comment->created_at->toISOString() : null,
                        ];
                    })
                    ->values()
                    ->toArray();
                $total = $post->comments->count();
                break;
                
            case 'reposts':
                $data = $post->reposts
                    ->sortByDesc('created_at')
                    ->map(function($repost) use ($getProfilePhotoUrl) {
                        return [
                            'id' => $repost->alumni->id ?? null,
                            'first_name' => $repost->alumni->first_name ?? 'Unknown',
                            'last_name' => $repost->alumni->last_name ?? '',
                            'profile_photo' => $getProfilePhotoUrl($repost->alumni),
                            'caption' => $repost->caption,
                            'created_at' => $repost->created_at ? $repost->created_at->toISOString() : null,
                        ];
                    })
                    ->values()
                    ->toArray();
                $total = $post->reposts->count();
                break;
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid interaction type. Allowed: likes, comments, reposts.'
                ], 400);
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'type' => $type,
        ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Post not found.'
        ], 404);
    } catch (\Exception $e) {
        \Log::error('Error fetching post interactions: ' . $e->getMessage(), [
            'post_id' => $postId,
            'type' => $request->input('type', 'likes'),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to load interactions. Please try again.'
        ], 500);
    }
}

/**
 * Get full post data with images and comments for moderation modal
 */
public function getFullPost($id)
{
    try {
        $post = \App\Models\Post::with([
            'alumni',
            'images',
            'comments.alumni',
            'reactions'
        ])->findOrFail($id);
        
        // Format images with full URLs
        $post->images->each(function($image) {
            $imagePath = ltrim($image->image_path, '/');
            $supabaseUrl = config('filesystems.disks.s3.url', '');
            if (empty($supabaseUrl)) {
                $supabaseUrl = rtrim(config('services.supabase.url', ''), '/') . '/storage/v1/object/public/luminus_assets/';
            } else {
                $supabaseUrl = rtrim($supabaseUrl, '/') . '/';
            }
            $image->full_url = $supabaseUrl . $imagePath;
        });
        
        return response()->json([
            'success' => true,
            'post' => $post
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Post not found'
        ], 404);
    }
}

}