@extends('layouts.admin')

@section('title', 'Add New Perk')

@section('content')
<div class="perks-create-container">
    <h2>Add New Perk</h2>

    <form action="{{ route('perks.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <label>Perk Title</label>
        <input type="text" name="PerkTitle" required>

        <label>Description</label>
        <textarea name="PerkDescription" required></textarea>

        <label>Validity Date</label>
        <input type="date" name="PerkValidity" required>

        <button type="submit" class="add-perks-button">
            Save Perk
        </button>
    </form>
</div>
@endsection