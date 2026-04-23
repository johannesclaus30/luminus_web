<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AnnouncementController extends Controller
{
    private const MAX_IMAGE_COUNT = 5;

    private const MAX_IMAGE_SIZE_KB = 5120;

    public function index()
    {
        $announcements = Announcement::with('images')
            ->where(function ($query) {
                $query->where('status', 1)->orWhereNull('status');
            })
            ->orderBy('date_posted', 'desc')
            ->paginate(5);
            
        return view('admin_announcements', compact('announcements'));
    }

    public function archived()
    {
        $announcements = Announcement::with('images')
            ->where('status', 0)
            ->orderBy('date_posted', 'desc')
            ->paginate(5);

        return view('admin_announcements', compact('announcements'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'announcement_description' => 'required|string|max:255',
            'scheduled_post_at' => 'nullable|date',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:' . self::MAX_IMAGE_SIZE_KB,
        ]);

        $uploadedImages = $this->normalizeUploadedImages($request->file('images'));

        if (count($uploadedImages) > self::MAX_IMAGE_COUNT) {
            throw ValidationException::withMessages([
                'images' => 'You can attach up to ' . self::MAX_IMAGE_COUNT . ' images only.',
            ]);
        }

        $adminId = $request->session()->get('admin_id');

        if (! $adminId) {
            abort(403);
        }

        $announcement = Announcement::create([
            'admin_id' => $adminId,
            'title' => $validated['title'],
            'announcement_description' => $validated['announcement_description'],
            'date_posted' => now(),
            'scheduled_post_at' => $validated['scheduled_post_at'] ?? null,
            'status' => 1,
        ]);

        if (! empty($uploadedImages)) {
            foreach ($uploadedImages as $image) {
                $path = $image->store('announcements', 'public');
                $announcement->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('announcements.index')->with('success', 'Announcement created successfully!');
    }

    /**
     * Show the form for editing (Perks Style)
     */
    public function edit(Announcement $announcement)
    {
        $announcement->load('images');

        return view('announcements.edit', compact('announcement'));
    }

    /**
     * Update the resource (Perks Style)
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'announcement_description' => 'required|string|max:255',
            'scheduled_post_at' => 'nullable|date',
            'deleted_media' => 'nullable|array',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:' . self::MAX_IMAGE_SIZE_KB,
        ]);

        $announcement->load('images');

        $deletedMediaIds = collect($request->input('deleted_media', []))
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->all();

        $remainingImageCount = $announcement->images
            ->filter(fn ($attachment) => $this->isImageAttachment($attachment->image_path))
            ->reject(fn ($attachment) => in_array((int) $attachment->id, $deletedMediaIds, true))
            ->count();

        $uploadedImages = $this->normalizeUploadedImages($request->file('images'));

        if (($remainingImageCount + count($uploadedImages)) > self::MAX_IMAGE_COUNT) {
            throw ValidationException::withMessages([
                'images' => 'You can keep or upload up to ' . self::MAX_IMAGE_COUNT . ' images total.',
            ]);
        }

        foreach ($deletedMediaIds as $mediaId) {
            $media = $announcement->images->firstWhere('id', $mediaId);

            if ($media) {
                Storage::disk('public')->delete($media->image_path);
                $media->delete();
            }
        }

        $announcement->update([
            'title' => $validated['title'],
            'announcement_description' => $validated['announcement_description'],
            'scheduled_post_at' => $validated['scheduled_post_at'] ?? null,
        ]);

        if (! empty($uploadedImages)) {
            foreach ($uploadedImages as $image) {
                $path = $image->store('announcements', 'public');
                $announcement->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->update(['status' => 0]);

        return redirect()->route('announcements.index')->with('success', 'Announcement archived.');
    }

    public function restore(Announcement $announcement)
    {
        $announcement->update(['status' => 1]);

        return redirect()->route('announcements.archived')->with('success', 'Announcement restored.');
    }

    private function normalizeUploadedImages(mixed $images): array
    {
        if (! $images) {
            return [];
        }

        if (! is_array($images)) {
            return [$images];
        }

        return array_values(array_filter($images));
    }

    private function isImageAttachment(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true);
    }
}