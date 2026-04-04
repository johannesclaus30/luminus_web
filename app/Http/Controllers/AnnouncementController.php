<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        // Added .with('images') to prevent N+1 query issues
        $announcements = Announcement::with('images')
            ->orderBy('DatePosted', 'desc')
            ->paginate(5);
            
        return view('admin_announcements', compact('announcements'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'AnnouncementTitle' => 'required|string|max:255',
            'AnnouncementDescription' => 'required|string|max:255',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'video' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:51200',
        ]);

        $announcement = Announcement::create([
            'AnnouncementTitle' => $request->AnnouncementTitle,
            'AnnouncementDescription' => $request->AnnouncementDescription,
            'DatePosted' => now(),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('announcements', 'public');
                $announcement->images()->create([
                    'ImagePath' => $path,
                    'UploadTime' => now(),
                ]);
            }
        }
        
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('announcements/videos', 'public');
            $announcement->images()->create([
                'ImagePath' => $videoPath,
                'UploadTime' => now(),
            ]);
        }

        return redirect()->route('announcements.index')->with('success', 'Announcement created successfully!');
    }

    /**
     * Show the form for editing (Perks Style)
     */
    public function edit(Announcement $announcement)
    {
        // No need to manual findOrFail, Laravel does it for you
        return view('announcements.edit', compact('announcement'));
    }

    /**
     * Update the resource (Perks Style)
     */
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'AnnouncementTitle' => 'required|string|max:255',
            'AnnouncementDescription' => 'required|string|max:255',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'video' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:51200',
        ]);

        // 1. HANDLE DELETIONS FIRST (Clean house before adding new stuff)
        if ($request->has('deleted_media')) {
            foreach ($request->deleted_media as $mediaId) {
                $media = $announcement->images()->find($mediaId);
                if ($media) {
                    Storage::disk('public')->delete($media->ImagePath);
                    $media->delete();
                }
            }
        }

        // 2. UPDATE TEXT FIELDS
        $announcement->update([
            'AnnouncementTitle' => $request->AnnouncementTitle,
            'AnnouncementDescription' => $request->AnnouncementDescription,
        ]);

        // 3. ADD NEW IMAGES
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('announcements', 'public');
                $announcement->images()->create([
                    'ImagePath' => $path,
                    'UploadTime' => now(),
                ]);
            }
        }
        
        // 4. ADD NEW VIDEO
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('announcements/videos', 'public');
            $announcement->images()->create([
                'ImagePath' => $videoPath,
                'UploadTime' => now(),
            ]);
        }

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    public function destroy(Announcement $announcement)
    {
        // You can implement deletion logic here later
        $announcement->delete();
        return redirect()->route('announcements.index')->with('success', 'Announcement deleted.');
    }
}