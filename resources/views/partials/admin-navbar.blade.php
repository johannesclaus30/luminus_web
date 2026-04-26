@php
    $admin = $currentAdmin ?? null;

    if (! $admin && request()->hasSession()) {
        $adminId = request()->session()->get('admin_id');

        if ($adminId) {
            $admin = \App\Models\Admin::query()->find($adminId);
        }
    }

    $adminName = trim((string) ($currentAdminName ?? request()->session()->get('admin_name', '')));

    if ($admin) {
        $resolvedName = trim((string) (($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')));

        if ($resolvedName !== '') {
            $adminName = $resolvedName;
        }
    }

    if ($adminName === '') {
        $adminName = 'Admin';
    }

    $photoUrl = $currentAdminPhotoUrl ?? null;

    if (! $photoUrl && $admin?->photo) {
        $photoPath = trim((string) $admin->photo);

        if (preg_match('/^https?:\/\//i', $photoPath)) {
            $photoUrl = $photoPath;
        } elseif (str_starts_with($photoPath, '/storage/')) {
            $photoUrl = $photoPath;
        } elseif (str_starts_with($photoPath, 'storage/')) {
            $photoUrl = '/' . $photoPath;
        } elseif (str_starts_with($photoPath, '/')) {
            $photoUrl = $photoPath;
        } else {
            $photoUrl = \Illuminate\Support\Facades\Storage::disk('supabase_admin')->url($photoPath);
        }
    }

    $firstName = trim((string) ($admin?->admin_first_name ?? ''));
    $lastName = trim((string) ($admin?->admin_last_name ?? ''));
    $initials = '';

    if ($firstName !== '') {
        $initials .= strtoupper(mb_substr($firstName, 0, 1));
    }

    if ($lastName !== '') {
        $initials .= strtoupper(mb_substr($lastName, 0, 1));
    }

    if ($initials === '') {
        $nameParts = preg_split('/\s+/', trim($adminName)) ?: [];

        if (count($nameParts) >= 2) {
            $initials = strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[count($nameParts) - 1], 0, 1));
        } else {
            $initials = strtoupper(mb_substr($adminName, 0, 2));
        }
    }
    $displayFirstName = $firstName !== '' ? $firstName : (explode(' ', $adminName)[0] ?? 'Admin');
@endphp

<nav class="nav-main">
    <img class="nav-logo" src="{{ asset('assets/logos/LumiNUs_Logo_Landscape_White.png') }}" alt="LumiNUs Logo">

    <div class="nav-admin-profile">
        <div class="nav-admin-avatar {{ $photoUrl ? 'has-photo' : 'is-initials' }}">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $adminName }} profile photo">
            @else
                <span>{{ $initials }}</span>
            @endif
        </div>

        <div class="nav-admin-greeting">
            <span>Hello,</span>
            <strong>{{ $displayFirstName }}</strong>
        </div>
    </div>
</nav>