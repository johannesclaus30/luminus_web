<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perks;        // FIX 1: Must be plural to match your Perks.php file
use App\Models\PerkImage;   
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PerksController extends Controller
{
    public function index()
    {
        // Use Perks:: (plural)
        // Exclude archived perks (status = 'archived') if present
        $perks = Perks::with('images')
            ->where(function ($q) {
                $q->where('status', '!=', 'archived')->orWhereNull('status');
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
            'status' => 'nullable|string',
            'images' => 'nullable|array|max:5', 
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Use Perks:: (plural)
        $perk = Perks::create([
            'title'       => $request->title,
            'description' => $request->description,
            'valid_until' => $request->valid_until,
            'status'      => $request->status ?? 'active',
            // For temporary testing when admin auth isn't wired, default to admin id 1
            'admin_id'    => Auth::id() ?? 4,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('perks', 'public');
                $perk->images()->create([
                    'image_path' => $path
                ]);
            }
        }

        return redirect()->route('perks.index')->with('success', 'Perk and gallery images created successfully.');
    }

    public function edit(Perks $perk) // Plural type-hint
    {
        $perk->load('images');
        return view('perks.edit', compact('perk'));
    }

    public function update(Request $request, Perks $perk) // Plural type-hint
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'valid_until' => 'required|date',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Prevent adding more than 5 images total (existing + new)
        if ($request->hasFile('images')) {
            $existingCount = $perk->images()->count();
            // account for images marked for removal
            $toRemove = $request->input('remove_existing', []);
            $existingAfterRemoval = max(0, $existingCount - count($toRemove));
            $newCount = count($request->file('images'));
            if ($existingAfterRemoval + $newCount > 5) {
                return back()->withErrors(['images' => 'You can upload up to 5 images total (including existing).'])->withInput();
            }
            // delete images marked for removal before adding new ones
            if (!empty($toRemove)) {
                foreach ($toRemove as $id) {
                    $img = $perk->images()->find($id);
                    if ($img) {
                        Storage::disk('public')->delete($img->image_path);
                        $img->delete();
                    }
                }
            }
        }

        $perk->update([
            'title'       => $request->title,
            'description' => $request->description,
            'valid_until' => $request->valid_until,
            'status'      => $request->status,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('perks', 'public');
                $perk->images()->create([
                    'image_path' => $path
                ]);
            }
        }

        return redirect()->route('perks.index')->with('success', 'Perk updated successfully.');
    }

    // FIX 2: Changed from (Perk $perk) to (Perks $perk)
    public function destroy(Perks $perk) 
    {
        // Archive by setting status to 'archived' instead of deleting the row.
        $perk->update(['status' => 'archived']);
        return redirect()->route('perks.index')->with('success', 'Perk archived.');
    }

    /**
     * Show archived perks (by `status = archived` if the column exists).
     */
    public function archived()
    {
        // Show perks marked with status = 'archived'
        $perks = Perks::with('images')
            ->where('status', 'archived')
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('admin_perks', compact('perks'));
    }

    /**
     * Restore (unarchive) a perk by setting status back to active.
     */
    public function restore(Perks $perk)
    {
        $perk->update(['status' => 'active']);
        return redirect()->route('perks.archived')->with('success', 'Perk restored.');
    }
}