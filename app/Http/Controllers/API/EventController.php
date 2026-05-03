<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ImagesEvent;
use Illuminate\Http\Request;

class EventController extends Controller
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

    private function buildEventPayload(Request $request, Event $event): array
    {
        $images = $event->images->map(function (ImagesEvent $image) use ($request) {
            $imageUrl = $this->resolveStorageUrl($request, $image->image_path);

            if ($image->updated_at?->timestamp) {
                $imageUrl .= (str_contains($imageUrl, '?') ? '&' : '?') . 'v=' . $image->updated_at->timestamp;
            }

            return [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'image_url' => $imageUrl,
            ];
        })->values();

        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'max_capacity' => $event->max_capacity,
            'status' => $event->status,
            'event_type' => $event->event_type,
            'platform' => $event->platform,
            'platform_url' => $event->platform_url,
            'venue' => [
                'id' => $event->venue?->id,
                'name' => $event->venue?->name,
                'address' => $event->venue?->address,
                'latitude' => $event->venue?->latitude,
                'longitude' => $event->venue?->longitude,
            ],
            'images' => $images,
            'cover_image_url' => $images->first()['image_url'] ?? null,
        ];
    }

    public function index(Request $request)
    {
        $events = Event::with([
            'venue:id,name,address,latitude,longitude',
            'images:id,event_id,image_path,updated_at',
        ])
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Event $event) => $this->buildEventPayload($request, $event))
            ->values();

        return response()->json([
            'events' => $events,
        ]);
    }
}