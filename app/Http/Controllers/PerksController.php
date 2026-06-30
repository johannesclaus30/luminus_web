<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perks;
use App\Models\PerkImage;   
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PerksController extends Controller
{
    public function index()
    {
        $perks = Perks::with('images')
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);
        return view('admin_perks', compact('perks'));
    }

    public function create()
    {
        return view('perks.create'); 
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'valid_until' => 'required|date',
            'status' => 'nullable',
            'images' => 'nullable|array|max:5', 
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'images.max' => 'You can only upload a maximum of 5 images.',
            'images.*.max' => 'Each image must not exceed 5MB.',
            'images.*.mimes' => 'Images must be JPG, PNG, or WEBP format.',
        ]);

        $perk = Perks::create([
            'title'       => $request->title,
            'description' => $request->description,
            'valid_until' => $request->valid_until,
            'status'      => $this->normalizeStatus($request->input('status'), 1),
            'admin_id'    => Auth::id() ?? 1,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->storePerkImage($perk, $file);
                $perk->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('perks.index')->with('success', 'Perk created successfully.');
    }

    public function edit(Perks $perk)
    {
        $perk->load('images');
        return view('perks.edit', compact('perk'));
    }

    public function update(Request $request, Perks $perk)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'valid_until' => 'required|date',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'images.max' => 'You can only upload a maximum of 5 images.',
            'images.*.max' => 'Each image must not exceed 5MB.',
            'images.*.mimes' => 'Images must be JPG, PNG, or WEBP format.',
        ]);

        $toRemove = $request->input('remove_existing', []);

        if (! empty($toRemove)) {
            foreach ($toRemove as $id) {
                $img = $perk->images()->find($id);

                if ($img) {
                    $this->deleteStoredImage($img->image_path);
                    $img->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            $existingCount = $perk->images()->count();
            $newCount = count($request->file('images'));

            if ($existingCount + $newCount > 5) {
                return back()->withErrors(['images' => 'You can upload up to 5 images total (including existing).'])->withInput();
            }
        }

        $perk->update([
            'title'       => $request->title,
            'description' => $request->description,
            'valid_until' => $request->valid_until,
            'status'      => $request->filled('status')
                ? $this->normalizeStatus($request->input('status'), $perk->status ?? 1)
                : $perk->status,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->storePerkImage($perk, $file);
                $perk->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('perks.index')->with('success', 'Perk updated successfully.');
    }

    public function destroy(Perks $perk) 
    {
        $perk->update(['status' => 0]);
        return redirect()->route('perks.index')->with('success', 'Perk archived.');
    }

    public function archived()
    {
        $perks = Perks::with('images')
            ->where('status', 0)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('admin_perks', compact('perks'));
    }

    public function restore(Perks $perk)
    {
        $perk->update(['status' => 1]);
        return redirect()->route('perks.archived')->with('success', 'Perk restored.');
    }

    public function permanentDelete(Perks $perk)
    {
        // Only allow permanent deletion of archived perks
        if ((int) $perk->status !== 0) {
            return redirect()->back()->with('error', 'Only archived perks can be permanently deleted.');
        }

        // Delete all associated image files from storage
        foreach ($perk->images as $media) {
            $this->deleteStoredImage($media->image_path);
            $media->delete();
        }

        // Hard delete the perk
        $perk->delete();

        return redirect()->route('perks.archived')->with('success', 'Perk permanently deleted.');
    }

    protected function storePerkImage(Perks $perk, $file): string
    {
        $directory = 'perks_images/' . $perk->id;
        $fileName = $this->buildAttachmentFileName($file, 'perk-image');

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

    protected function buildAttachmentFileName($file, string $fallbackPrefix): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $name = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($name);

        if ($slug === '') {
            $slug = $fallbackPrefix;
        }

        return $slug . '-' . Str::random(12) . '.' . $extension;
    }

    protected function normalizeStatus($status, int $default = 1): int
    {
        if ($status === null || $status === '') {
            return $default;
        }

        if (is_int($status) || ctype_digit((string) $status)) {
            return (int) $status;
        }

        return strtolower((string) $status) === 'inactive' ? 0 : 1;
    }
}