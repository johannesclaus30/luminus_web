@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/perks.css') }}">
@endpush

@section('title', 'Edit Perk')

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
                <h2>Edit Perk</h2>

                <form action="{{ route('perks.update', $perk->id) }}" method="POST" enctype="multipart/form-data">

                    <div class="perks-details">
                        <div class="new-perks-title-description">
                            @csrf
                            @method('PUT')
                            <label>Perk Title</label>
                            <input class="textarea-style" type="text" name="title" value="{{ old('title', $perk->title) }}" required>
                            <label>Description</label>
                            <textarea class="textarea-style textarea-description" name="description" required>{{ old('description', $perk->description) }}</textarea>

                            <label>Status</label>
                            <select name="status" class="textarea-style">
                                <option value="active" {{ (old('status', $perk->status) == 'active') ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ (old('status', $perk->status) == 'inactive') ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="date-image-container">

                            <div class="perks-image-container">
                                <p class="perks-image-text">Attachments:</p>

                                <label class="image-upload-box" data-existing="{{ $perk->images->count() }}">
                                    <input
                                        type="file"
                                        name="images[]"
                                        accept="image/*"
                                        multiple
                                        onchange="previewPerkImages(event)"
                                        hidden
                                    >
                                    <div id="perkImagesPreview" class="perk-images-preview">
                                        @if($perk->images && $perk->images->isNotEmpty())
                                            @foreach($perk->images as $image)
                                                <div class="preview-item existing" data-image-id="{{ $image->id }}" style="position:relative; display:inline-block;">
                                                    <img
                                                        src="{{ asset('storage/' . $image->image_path) }}"
                                                        alt="Perk Image"
                                                        class="perk-image"
                                                        style="max-width:90px; max-height:90px; object-fit:cover; display:block;"
                                                    >
                                                    <button type="button" class="remove-existing" data-id="{{ $image->id }}" title="Remove image" style="position:absolute; top:6px; right:6px; background:#fff; border-radius:50%; border:1px solid #ccc; width:26px; height:26px; line-height:22px; text-align:center;">&times;</button>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <span id="uploadPlaceholder" style="{{ ($perk->images && $perk->images->isNotEmpty()) ? 'display:none;' : '' }}">Click to upload images (you can pick multiple)</span>
                                    <div class="attachment-counter" aria-hidden="true">{{ $perk->images->count() }}/5</div>
                                </label>

                            </div>

                            <label>Validity Date</label>

                            <div class="date-input-wrapper">
                                <input 
                                    type="date"
                                    name="valid_until"
                                    value="{{ old('valid_until', $perk->valid_until ? $perk->valid_until->format('Y-m-d') : '') }}"
                                    min="{{ date('Y-m-d') }}"
                                    required
                                >
                            </div>

                            <div class="form-action-buttons">
                                <button type="submit" class="perk-submit-btn">
                                    Update Perk
                                </button>
                                <button type="button" class="discard-btn" onclick="location.href='{{ route('perks.index') }}'">Discard</button>
                            </div>
                        </div>
                        
                    </div>

                </form>
            </div>
        </div>
    </div>

<script>
function previewPerkImages(event) {
    const input = event.target;
    const uploadBox = input.closest('.image-upload-box');
    const previewContainer = uploadBox.querySelector('.perk-images-preview');
    const placeholder = uploadBox.querySelector('#uploadPlaceholder');
    const counter = uploadBox.querySelector('.attachment-counter');

    if (!uploadBox._dt) uploadBox._dt = new DataTransfer();
    if (typeof uploadBox.dataset.existing === 'undefined') uploadBox.dataset.existing = 0;

    const existing = parseInt(uploadBox.dataset.existing || 0, 10);
    const files = input.files ? Array.from(input.files) : [];

    for (const file of files) {
        if (existing + uploadBox._dt.files.length + 1 > 5) {
            alert('You can upload up to 5 images total (including existing).');
            input.value = '';
            break;
        }
        uploadBox._dt.items.add(file);
    }

    input.files = uploadBox._dt.files;

    // render only new previews
    Array.from(previewContainer.querySelectorAll('.new-preview')).forEach(n => n.remove());

    if (uploadBox._dt.files.length) {
        placeholder.style.display = 'none';
        Array.from(uploadBox._dt.files).forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement('div');
                wrapper.className = 'preview-item new-preview';
                wrapper.style.position = 'relative';
                wrapper.style.display = 'inline-block';
                wrapper.style.marginRight = '8px';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'perk-image';
                img.style.maxWidth = '120px';
                img.style.marginBottom = '8px';
                img.style.display = 'block';

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'remove-new';
                btn.textContent = '×';
                btn.title = 'Remove image';
                btn.style.position = 'absolute';
                btn.style.top = '6px';
                btn.style.right = '6px';
                btn.style.background = '#fff';
                btn.style.border = '1px solid #ccc';
                btn.style.borderRadius = '50%';
                btn.style.width = '26px';
                btn.style.height = '26px';

                btn.addEventListener('click', function() {
                    for (let i = 0; i < uploadBox._dt.items.length; i++) {
                        const it = uploadBox._dt.items[i];
                        if (it.getAsFile().name === file.name && it.getAsFile().size === file.size) {
                            uploadBox._dt.items.remove(i);
                            input.files = uploadBox._dt.files;
                            wrapper.remove();
                            counter.textContent = `${existing + uploadBox._dt.files.length}/5`;
                            break;
                        }
                    }
                });

                wrapper.appendChild(img);
                wrapper.appendChild(btn);
                previewContainer.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    } else {
        if (!previewContainer.children.length) placeholder.style.display = 'inline';
    }

    counter.textContent = `${existing + uploadBox._dt.files.length}/5`;
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // delegate handler for existing-image remove buttons (toggle deletion)
    document.querySelectorAll('.remove-existing').forEach(btn => {
        btn.addEventListener('click', function() {
            const wrapper = btn.closest('.preview-item.existing');
            const imageId = btn.dataset.id;
            const form = btn.closest('form');

            if (wrapper.classList.contains('marked-for-removal')) {
                // undo removal
                wrapper.classList.remove('marked-for-removal');
                wrapper.style.opacity = '1';
                // remove hidden input
                const hidden = form.querySelector('input[name="remove_existing[]"][value="' + imageId + '"]');
                if (hidden) hidden.remove();
                // update dataset existing count
                const uploadBox = form.querySelector('.image-upload-box');
                if (uploadBox) uploadBox.dataset.existing = parseInt(uploadBox.dataset.existing || 0, 10) + 1;
            } else {
                // mark for removal
                wrapper.classList.add('marked-for-removal');
                wrapper.style.opacity = '0.4';
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'remove_existing[]';
                hidden.value = imageId;
                form.appendChild(hidden);
                const uploadBox = form.querySelector('.image-upload-box');
                if (uploadBox) uploadBox.dataset.existing = Math.max(0, parseInt(uploadBox.dataset.existing || 0, 10) - 1);
            }

            // update counter display
            const uploadBox = btn.closest('.image-upload-box');
            if (uploadBox) {
                const counter = uploadBox.querySelector('.attachment-counter');
                const existing = parseInt(uploadBox.dataset.existing || 0, 10);
                const newCount = uploadBox._dt ? uploadBox._dt.files.length : 0;
                if (counter) counter.textContent = `${existing + newCount}/5`;
            }
        });
    });
});
</script>

@endsection