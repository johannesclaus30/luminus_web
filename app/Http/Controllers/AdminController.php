<?php

namespace App\Http\Controllers;

use App\Models\Admin; // Import the Model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Fetch all admins from the 'admins' table
        $admins = Admin::all();

        // 2. Pass the variable to the view
        // Make sure the view name matches your file: 'admin_directory'
        return view('admin_directory', compact('admins'));
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
