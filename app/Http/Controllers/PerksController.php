<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perks;

class PerksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perks = Perks::orderBy('created_at', 'desc')->paginate(2);
        return view('admin_perks', compact('perks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('perks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'PerkTitle' => 'required|string|max:255',
            'PerkDescription' => 'required|string',
            'PerkValidity' => 'required|date'
        ]);

        Perks::create([
            'PerkTitle' => $request->PerkTitle,
            'PerkDescription' => $request->PerkDescription,
            'PerkValidity' => $request->PerkValidity
        ]);

        return redirect()->route('perks.index')->with('success', 'Perk created successfully.');
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
