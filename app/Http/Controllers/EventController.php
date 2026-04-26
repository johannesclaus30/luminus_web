<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['images', 'admin', 'venue'])
            ->where(function ($query) {
                $query->where('status', 1)->orWhereNull('status');
            })
            ->orderByDesc('start_date')
            ->paginate(5);
            
        return view('admin_events', compact('events'));
    }

    public function archived()
    {
        $events = Event::with(['images', 'admin', 'venue'])
            ->where('status', 0)
            ->orderByDesc('start_date')
            ->paginate(5);

        return view('admin_events', compact('events'));
    }

    public function create()
    {
        $event = new Event();

        return view('events.create', compact('event'));
    }

    public function store(Request $request)
    {
        $this->prepareVenueAndPlatformPayload($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_capacity' => 'required|integer|min:1',
            'event_type' => 'required|in:In-Person,Online,Hybrid',
            'platform' => 'required_if:event_type,Online,Hybrid|nullable|string|max:255',
            'platform_url' => 'nullable|url|max:255',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'required_if:event_type,In-Person,Hybrid|nullable|string|max:255',
            'venue_latitude' => 'nullable|numeric|between:-90,90',
            'venue_longitude' => 'nullable|numeric|between:-180,180',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $adminId = $request->session()->get('admin_id');

        abort_unless($adminId, 403);

        $venueId = $this->syncVenue($request);

        $event = Event::create([
            'admin_id' => $adminId,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'max_capacity' => $request->max_capacity,
            'event_type' => $request->event_type,
            'platform' => $request->platform,
            'platform_url' => $request->platform_url,
            'venue_id' => $venueId,
            'status' => 1,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->storeEventImage($event, $file);

                $event->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('events.index')->with('success', 'Event published successfully!');
    }

    public function edit(Event $event)
    {
        $event->load(['images', 'admin', 'venue']);

        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->prepareVenueAndPlatformPayload($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_capacity' => 'required|integer|min:1',
            'event_type' => 'required|in:In-Person,Online,Hybrid',
            'platform' => 'required_if:event_type,Online,Hybrid|nullable|string|max:255',
            'platform_url' => 'nullable|url|max:255',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'required_if:event_type,In-Person,Hybrid|nullable|string|max:255',
            'venue_latitude' => 'nullable|numeric|between:-90,90',
            'venue_longitude' => 'nullable|numeric|between:-180,180',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->has('deleted_media')) {
            foreach ($request->deleted_media as $mediaId) {
                $media = $event->images()->find($mediaId);
                if ($media) {
                    $this->deleteStoredImage($media->image_path);
                    $media->delete();
                }
            }
        }

        $venueId = $this->syncVenue($request, $event->venue_id);

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'max_capacity' => $request->max_capacity,
            'event_type' => $request->event_type,
            'platform' => $request->platform,
            'platform_url' => $request->platform_url,
            'venue_id' => $venueId,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $this->storeEventImage($event, $image);
                $event->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()
            ->route('events.index')
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        $event->update(['status' => 0]);

        return redirect()->route('events.index')->with('success', 'Event successfully archived!');
    }

    public function restore(Event $event)
    {
        $event->update(['status' => 1]);

        return redirect()->route('events.archived')->with('success', 'Event restored.');
    }

    protected function syncVenue(Request $request, ?int $existingVenueId = null): ?int
    {
        $address = trim((string) $request->input('venue_address', ''));
        $latitude = $request->input('venue_latitude');
        $longitude = $request->input('venue_longitude');

        if ($address === '' || $latitude === null || $longitude === null) {
            return null;
        }

        $venue = $existingVenueId ? Venue::find($existingVenueId) : null;
        $venueData = [
            'name' => trim((string) $request->input('venue_name', $address)) ?: $address,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if ($venue) {
            $venue->update($venueData);

            return $venue->getKey();
        }

        return Venue::create($venueData)->getKey();
    }

    protected function prepareVenueAndPlatformPayload(Request $request): void
    {
        $eventType = $request->input('event_type');

        if ($eventType === 'Online') {
            $request->merge([
                'venue_name' => null,
                'venue_address' => null,
                'venue_latitude' => null,
                'venue_longitude' => null,
            ]);

            return;
        }

        if ($eventType === 'In-Person') {
            $request->merge([
                'platform' => null,
                'platform_url' => null,
            ]);
        }
    }

    protected function storeEventImage(Event $event, $file): string
    {
        $directory = 'events_images/' . $event->id;
        $fileName = $this->buildEventImageFileName($file);

        Storage::disk('supabase_admin')->putFileAs($directory, $file, $fileName, 'public');

        return $directory . '/' . $fileName;
    }

    protected function deleteStoredImage(?string $imagePath): void
    {
        $path = trim((string) $imagePath);

        if ($path === '') {
            return;
        }

        foreach (['public', 'supabase_admin'] as $diskName) {
            $disk = Storage::disk($diskName);

            if ($disk->exists($path)) {
                $disk->delete($path);
                return;
            }
        }
    }

    protected function buildEventImageFileName($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $name = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($name);

        if ($slug === '') {
            $slug = 'event-image';
        }

        return $slug . '-' . Str::random(12) . '.' . $extension;
    }
}