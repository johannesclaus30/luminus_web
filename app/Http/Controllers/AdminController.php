<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Alumni;
use App\Models\AdminPermission;
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
        // Fetch all alumni for the grid
        $alumni = Alumni::query()->latest('created_at')->get();

        // --- DIRECTORY STATS ---
        
        // 1. Total Alumni Count
        $totalAlumni = Alumni::count(); 
        // Note: If you only want to count verified alumni, use: 
        // $totalAlumni = Alumni::where('verification_status', 'verified')->count();

        // 2. Recent Graduates (Alumni who graduated in the current year)
        $recentGraduates = Alumni::whereYear('year_graduated', now()->year)->count();

        // 3. Unique Programs Offered
        $uniquePrograms = Alumni::whereNotNull('program')
            ->where('program', '!=', '')
            ->distinct('program')
            ->count('program');

        // 4. Alumni With Email Addresses
        $withEmails = Alumni::whereNotNull('email')
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

        // Generate a random 10-character temporary password
        $temporaryPassword = Str::random(10);

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
        $alumnus = Alumni::findOrFail($id);
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


}