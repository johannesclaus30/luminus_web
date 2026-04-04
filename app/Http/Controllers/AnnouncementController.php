<?php

namespace App\Http\Controllers;


use App\Models\Announcement;
use App\Models\ImagesAnnouncement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::orderBy('DatePosted', 'desc')
            ->paginate(5);
        return view('admin_announcements', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('announcements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // VALIDATE
        $request->validate([
            'AnnouncementTitle' => 'required|string|max:255',
            'AnnouncementDescription' => 'required|string|max:255',

            // IMAGES UP TO 5
            'images.*' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif|max:2048', // 2MB max per image

            // VIDEO ONLY 1 ALLOWED
            'video' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:51200', // 50MB max for video
        ]);
        // SAVE ANNOUNCEMENT
        $announcement = Announcement::create([
            'AnnouncementTitle' => $request->AnnouncementTitle,
            'AnnouncementDescription' => $request->AnnouncementDescription,
            'DatePosted' => now(),
        ]);

        // SAVE IMAGES
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {

                // store image in storage/app/public/announcements
                $path = $image->store('announcements', 'public');

                $announcement->images()->create([
                    'ImagePath' => $path,
                    'UploadTime' => now(),
                ]);
            }
        }
        
        // SAVE VIDEO (only one allowed)
        if ($request->hasFile('video')) {

            $videoPath = $request->file('video')
                                ->store('announcements/videos', 'public');

            $announcement->images()->create([
                'ImagePath' => $videoPath,
                'UploadTime' => now(),
            ]);
        }


        // REDIRECT BACK
        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement created successfully!');

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
