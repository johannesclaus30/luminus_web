<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validate the incoming data from React Native
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'sex' => 'required|string',
            'year_graduated' => 'required|date',
            'student_id_number' => 'required|string|unique:alumnis,student_id_number',
            'email' => 'required|string|email|max:255|unique:alumnis,email',
            'password' => 'required|string|min:8', // We will hash this before saving
        ]);

        // 2. Create the new Alumni in Supabase
        $alumni = Alumni::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name, // Optional
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'sex' => $request->sex,
            'year_graduated' => $request->year_graduated,
            'student_id_number' => $request->student_id_number,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password), // Securely hash the password
        ]);

        // 3. Generate the API Token for the mobile app
        $token = $alumni->createToken('mobile-app-token')->plainTextToken;

        // 4. Return the success response
        return response()->json([
            'alumni' => $alumni,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        // 1. Validate the login request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Find the Alumni by email
        $alumni = Alumni::where('email', $request->email)->first();

        // 3. Check if Alumni exists and password matches
        if (!$alumni || !Hash::check($request->password, $alumni->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // 4. Generate a new API Token
        $token = $alumni->createToken('mobile-app-token')->plainTextToken;

        // 5. Return the alumni data and token
        return response()->json([
            'alumni' => $alumni,
            'token' => $token
        ]);
    }

    public function savePushToken(Request $request)
{
    $request->validate(['token' => 'required|string']);
    
    $alumni = $request->user();
    $alumni->expo_push_token = $request->token;
    $alumni->save();

    return response()->json(['message' => 'Token saved successfully']);
}

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $alumni = Alumni::where('email', $request->email)->first();

        if (!$alumni) {
            throw ValidationException::withMessages([
                'email' => ['We could not find an account with that email address.'],
            ]);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $alumni->email],
            [
                'token' => hash('sha256', $token),
                'created_at' => now(),
            ]
        );

        Mail::raw(
            "Use this password reset token for your LumiNUs account:\n\n{$token}\n\nThis token expires in 60 minutes.",
            function ($message) use ($alumni) {
                $message->to($alumni->email)->subject('LumiNUs Password Reset');
            }
        );

        return response()->json([
            'message' => 'Password reset instructions have been sent to your email address.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'email' => ['This reset token is invalid or has expired.'],
            ]);
        }

        $isExpired = $record->created_at
            ? Carbon::parse($record->created_at)->addMinutes(60)->isPast()
            : true;

        if ($isExpired || !hash_equals($record->token, hash('sha256', $request->token))) {
            throw ValidationException::withMessages([
                'token' => ['This reset token is invalid or has expired.'],
            ]);
        }

        $alumni = Alumni::where('email', $request->email)->first();

        if (!$alumni) {
            throw ValidationException::withMessages([
                'email' => ['We could not find an account with that email address.'],
            ]);
        }

        $alumni->forceFill([
            'password_hash' => Hash::make($request->password),
        ])->save();

        DB::table('password_reset_tokens')
            ->where('email', $alumni->email)
            ->delete();

        return response()->json([
            'message' => 'Your password has been reset successfully.',
        ]);
    }

    public function resetAccountPassword(Request $request)
    {
        $alumni = $request->user();

        $request->validate([
            'student_id_number' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($request->student_id_number !== $alumni->student_id_number) {
            throw ValidationException::withMessages([
                'student_id_number' => ['The student number does not match this account.'],
            ]);
        }

        $alumni->forceFill([
            'password_hash' => Hash::make($request->password),
        ])->save();

        return response()->json([
            'message' => 'Your password has been updated successfully.',
        ]);
    }
}