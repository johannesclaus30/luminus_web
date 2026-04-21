<?php

namespace App\Http\Controllers;

use App\Models\Admin; // Import the Model
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alumni = Alumni::query()->latest('created_at')->get();

        return view('admin_directory', compact('alumni'));
    }

    public function settings()
    {
        $admins = Admin::query()->latest('created_at')->get();

        return view('admin_settings', compact('admins'));
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

        $temporaryPassword = Str::random(12);
        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('admin-photos', 'public');
        }

        Admin::create([
            'admin_first_name' => $validated['admin_first_name'],
            'admin_middle_name' => $validated['admin_middle_name'] ?? null,
            'admin_last_name' => $validated['admin_last_name'],
            'admin_email' => $validated['admin_email'],
            'phone_number' => $validated['phone_number'],
            'photo' => $photoPath,
            'admin_password_hash' => Hash::make($temporaryPassword),
            'admin_role' => $validated['admin_role'],
        ]);

        return redirect()
            ->route('admin.settings', ['section' => 'add-admin'])
            ->with('status', 'Admin account created successfully.')
            ->with('temporary_password', $temporaryPassword);
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
}
