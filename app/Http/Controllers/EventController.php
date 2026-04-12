<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        // Fetch events with their images relationship
        // Ordered by StartDate so the nearest events show first
        $events = Event::with('images')
            ->where('Status', 'Active') 
            ->orderBy('StartDate', 'desc')
            ->paginate(5);
            
        return view('admin_events', compact('events'));
    }

    public function create()
    {
        // This creates an empty instance so the variable exists in your Blade file
        $event = new \App\Models\Event(); 

        return view('events.create', compact('event'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Title' => 'required|string|max:255',
            'Location' => 'required|string|max:255',
            'MaxCapacity' => 'required|integer|min:1',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
            'Description' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 1. Save the Event
        $event = Event::create([
            'Admin_ID' => auth()->id() ?? 1, // Fallback to 1 for dev/testing

            'Title' => $request->Title,
            'Description' => $request->Description,
            'StartDate' => $request->StartDate,
            'EndDate' => $request->EndDate,
            'Location' => $request->Location,
            'MaxCapacity' => $request->MaxCapacity,
        ]);

        // 2. Save Multiple Images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('events/posters', 'public');

                $event->images()->create([
                    'ImagePath' => $path,
                    'CreatedAt' => now(),
                ]);
            }
        }

        return redirect()->route('events.index')->with('success', 'Event published successfully!');
    }

    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'required|string',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
            'Location' => 'required|string|max:255',
            'MaxCapacity' => 'required|integer|min:1',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // 1. Handle Media Deletions (Perks/Announcements Style)
        if ($request->has('deleted_media')) {
            foreach ($request->deleted_media as $mediaId) {
                $media = $event->images()->find($mediaId);
                if ($media) {
                    Storage::disk('public')->delete($media->ImagePath);
                    $media->delete();
                }
            }
        }

        // 2. Update Event Details
        $event->update([
            'Title' => $request->Title,
            'Description' => $request->Description,
            'StartDate' => $request->StartDate,
            'EndDate' => $request->EndDate,
            'Location' => $request->Location,
            'MaxCapacity' => $request->MaxCapacity,
        ]);

        // 3. Add New Images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('events/images', 'public');
                $event->images()->create([
                    'ImagePath' => $path,
                    'CreatedAt' => now(),
                ]);
            }
        }

        return redirect()
            ->route('events.index')
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        // Update the status instead of deleting the row
        $event->update(['Status' => 'Archived']);

        return redirect()->route('events.index')->with('success', 'Event successfully archived!');
    }
}