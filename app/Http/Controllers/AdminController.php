<?php

namespace App\Http\Controllers;

use App\Models\Admin; // Import the Model
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

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

        $storedPassword = (string) ($admin->admin_password_hash ?? '');
        $isHashedPassword = password_get_info($storedPassword)['algo'] !== 0;

        $passwordMatches = $admin && (
            $storedPassword === $credentials['password'] ||
            ($isHashedPassword && Hash::check($credentials['password'], $storedPassword))
        );

        if (! $passwordMatches) {
            throw ValidationException::withMessages([
                'admin_email' => 'The provided admin credentials are incorrect.',
            ]);
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
        $alumni = Alumni::query()->latest('created_at')->get();

        return view('admin_directory', compact('alumni'));
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
     * Store a newly created resource in storage.
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
            'admin_role' => ['required', Rule::in(['super_admin', 'moderator', 'event_coordinator'])],
        ]);

        $temporaryPassword = 'password123';
        $admin = Admin::create([
            'admin_first_name' => $validated['admin_first_name'],
            'admin_middle_name' => $validated['admin_middle_name'] ?? null,
            'admin_last_name' => $validated['admin_last_name'],
            'admin_email' => $validated['admin_email'],
            'phone_number' => $validated['phone_number'],
            'photo' => null,
            'admin_password_hash' => $temporaryPassword,
            'admin_role' => $validated['admin_role'],
        ]);

        if ($request->hasFile('photo')) {
            $admin->photo = $this->storeAdminPhoto($request, 'photo', $admin, null);
            $admin->save();
        }

        return redirect()
            ->route('admin.settings', ['section' => 'add-admin'])
            ->with('status', 'Admin account created successfully.')
            ->with('temporary_password', $temporaryPassword);
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
            'admin_last_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('admins', 'admin_email')->ignore($admin->id)],
            'phone_number' => ['required', 'string', 'max:50'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('photo')) {
            $admin->photo = $this->storeAdminPhoto($request, 'photo', $admin, $admin->photo);
        }

        $admin->admin_first_name = $validated['admin_first_name'];
        $admin->admin_middle_name = $validated['admin_middle_name'] ?? null;
        $admin->admin_last_name = $validated['admin_last_name'];
        $admin->admin_email = $validated['admin_email'];
        $admin->phone_number = $validated['phone_number'];
        $admin->save();

        $request->session()->put([
            'admin_email' => $admin->admin_email,
            'admin_name' => trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
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
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', 'string', 'max:50'],
            'year_graduated' => ['nullable', 'date'],
            'student_id_number' => ['required', 'string', 'max:255', Rule::unique('alumnis', 'student_id_number')],
            'email' => ['required', 'email', 'max:255', Rule::unique('alumnis', 'email')],
            'phone_number' => ['required', 'string', 'max:50'],
            'program' => ['required', 'string', 'max:255'],
            'card_photo' => ['nullable', 'image', 'max:4096'],
        ]);

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

        Alumni::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'year_graduated' => $validated['year_graduated'] ?? null,
            'student_id_number' => $validated['student_id_number'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password_hash' => 'password123',
            'verification_status' => 'pending',
            'program' => $validated['program'],
            'card_photo' => $cardPhotoPath,
        ]);

        return redirect()
            ->route('admin.directory')
            ->with('status', 'Alumni account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    protected function getAuthenticatedAdmin(Request $request): ?Admin
    {
        $adminId = $request->session()->get('admin_id');

        if ($adminId) {
            $admin = Admin::query()->where('id', $adminId)->first();

            if ($admin) {
                return $admin;
            }
        }

        $adminEmail = $request->session()->get('admin_email');

        if ($adminEmail) {
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
}
