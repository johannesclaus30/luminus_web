<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\ImagesEvent;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventRegistrationController extends Controller
{
    private function resolveStorageUrl(Request $request, string $imagePath): string
    {
        $trimmedPath = trim($imagePath);

        if ($trimmedPath === '') {
            return $trimmedPath;
        }

        if (preg_match('/^https?:\/\//i', $trimmedPath)) {
            return $trimmedPath;
        }

        $normalizedPath = ltrim($trimmedPath, '/');
        $bucketName = trim((string) config('filesystems.disks.supabase.bucket', ''), '/');
        $publicBaseUrl = rtrim((string) config('filesystems.disks.supabase.url', ''), '/');

        if ($bucketName !== '' && str_starts_with($normalizedPath, $bucketName . '/')) {
            $normalizedPath = substr($normalizedPath, strlen($bucketName) + 1);
        }

        if ($publicBaseUrl !== '' && $bucketName !== '') {
            return $publicBaseUrl . '/' . $bucketName . '/' . ltrim($normalizedPath, '/');
        }

        return rtrim($request->getSchemeAndHttpHost(), '/') . '/' . ltrim($normalizedPath, '/');
    }

    public function index(Request $request)
    {
        $alumni = $request->user();

        if (!$alumni) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $registrations = EventRegistration::query()
            ->with([
                'event.venue:id,name,address,latitude,longitude',
                'event.images:id,event_id,image_path,updated_at',
            ])
            ->where('alumni_id', $alumni->id)
            ->orderByDesc('created_at')
            ->get(['id', 'event_id', 'alumni_id', 'rsvp_date', 'registration_confirmation', 'status', 'created_at'])
            ->map(function (EventRegistration $registration) use ($request) {
                $images = $registration->event?->images?->map(function (ImagesEvent $image) use ($request) {
                    $imageUrl = $this->resolveStorageUrl($request, $image->image_path);

                    if ($image->updated_at?->timestamp) {
                        $imageUrl .= (str_contains($imageUrl, '?') ? '&' : '?') . 'v=' . $image->updated_at->timestamp;
                    }

                    return [
                        'id' => $image->id,
                        'image_path' => $image->image_path,
                        'image_url' => $imageUrl,
                    ];
                })->values() ?? collect();

                return [
                    'id' => $registration->id,
                    'event_id' => $registration->event_id,
                    'rsvp_date' => $registration->rsvp_date,
                    'registration_confirmation' => $registration->registration_confirmation,
                    'status' => $registration->status,
                    'created_at' => $registration->created_at,
                    'event' => [
                        'id' => $registration->event?->id,
                        'title' => $registration->event?->title,
                        'start_date' => $registration->event?->start_date,
                        'end_date' => $registration->event?->end_date,
                        'max_capacity' => $registration->event?->max_capacity,
                        'event_type' => $registration->event?->event_type,
                        'platform' => $registration->event?->platform,
                        'platform_url' => $registration->event?->platform_url,
                        'venue' => [
                            'name' => $registration->event?->venue?->name,
                            'address' => $registration->event?->venue?->address,
                            'latitude' => $registration->event?->venue?->latitude,
                            'longitude' => $registration->event?->venue?->longitude,
                        ],
                        'images' => $images,
                        'cover_image_url' => $images->first()['image_url'] ?? null,
                    ],
                ];
            })
            ->values();

        return response()->json([
            'registrations' => $registrations,
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $alumni = $request->user();

        if (!$alumni) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $request->validate([
            'privacy_consent' => 'required|accepted',
            'attendance_consent' => 'required|accepted',
            'email' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $alreadyRegistered = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('alumni_id', $alumni->id)
            ->exists();

        if ($alreadyRegistered) {
            throw ValidationException::withMessages([
                'event_id' => ['You are already registered for this event.'],
            ]);
        }

        try {
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'alumni_id' => $alumni->id,
                'rsvp_date' => now()->toDateString(),
                'registration_confirmation' => true,
                'status' => 1,
            ]);
        } catch (QueryException $exception) {
            return response()->json([
                'message' => 'Unable to save your registration right now.',
            ], 500);
        }

        return response()->json([
            'message' => 'Event registration saved successfully.',
            'registration' => $registration,
        ], 201);
    }

    public function destroy(Request $request, Event $event)
    {
        $alumni = $request->user();

        if (!$alumni) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $registration = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('alumni_id', $alumni->id)
            ->first();

        if (!$registration) {
            return response()->json([
                'message' => 'Registration not found.',
            ], 404);
        }

        try {
            $registration->delete();
        } catch (QueryException $exception) {
            return response()->json([
                'message' => 'Unable to remove your registration right now.',
            ], 500);
        }

        return response()->json([
            'message' => 'Event registration removed successfully.',
        ]);
    }
}