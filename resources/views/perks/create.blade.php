@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/perks.css') }}">
@endpush

@section('title', 'Add New Perk')

@section('content')


    <div class="layout-wrapper">
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>

                <a href="/admin/dashboard" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="/admin/directory" class="admin-menu-buttons">Alumni Directory</a>
                <a href="/admin/announcements" class="admin-menu-buttons">Announcement Editor</a>
                <a href="/admin/events" class="admin-menu-buttons">Event Organizer</a>
                <a href="/admin/perks" class="admin-menu-current">Perks and Discounts</a>
                <a href="/admin/alumni_tracer" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="/admin/messages" class="admin-menu-buttons">Messages</a>
                <a href="/admin/settings" class="admin-menu-buttons">Settings</a>
                <a href="/admin/testing" class="admin-menu-buttons">Users Testing</a>
            </div>

            <a href="/admin/login" class="admin-menu-signout">Sign Out</a>
        </div>
        <div class="div-dashboard-container">
            <div class="perks-create-container">
                <h2>Add New Perks and Discounts</h2>

                <form action="{{ route('perks.store') }}" method="POST" enctype="multipart/form-data">
                    

                    <div class="perks-details">
                        <div class="new-perks-title-description">
                            @csrf
                            <label>Perk Title</label>
                            <input class="textarea-style" type="text" name="PerkTitle" required>
                            <label>Description</label>
                            <textarea class="textarea-style textarea-description" name="PerkDescription" required></textarea>
                        </div>
                        <div class="date-image-container">

                            <div class="perks-image-container">
                                <p class="perks-image-text">Attachments:</p>
                                <label class="image-upload-box">
                                    <input
                                        type="file"
                                        name="PerkImage" 
                                        accept="image/*" 
                                        onchange="previewPerkImage(event)"
                                        hidden
                                        >
                                    <img 
                                        id="perkImagePreview"
                                        src=""
                                        style="display: none;"
                                        class="perk-image"
                                        alt="Image Preview"
                                        >
                                    <span id="uploadPlaceholder">Click to upload image</span>
                                </label>


                            </div>
                            <label>Validity Date</label>
                            {{-- <input type="date" name="PerkValidity" required> --}}

                            <div class="date-input-wrapper">
                                <input 
                                    type="date"
                                    name="PerkValidity"
                                    min="{{ date('Y-m-d') }}"
                                    required
                                    >
                                    {{-- <span class="calendar-icon">📅</span> --}}
                            </div>

                            <button type="submit" class="perk-submit-btn">
                                Save Perk
                            </button>
                        </div>
                        
                    </div>

                </form>
            </div>
        </div>
    </div>

<script>
function previewPerkImage(event) {
    const input = event.target;
    const preview = document.getElementById('perkImagePreview');
    const placeholder = document.getElementById('uploadPlaceholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };

        reader.readAsDataURL(input.files[0]);
    }
}
</script>

@endsection
