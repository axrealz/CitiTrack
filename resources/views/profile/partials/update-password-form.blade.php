<form method="POST" action="{{ route('password.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <x-form-input
        name="current_password"
        label="Current Password"
        type="password"
        autocomplete="current-password"
        required
    />

    <x-form-input
        name="password"
        label="New Password"
        type="password"
        autocomplete="new-password"
        required
        hint="Minimum 8 characters"
    />

    <x-form-input
        name="password_confirmation"
        label="Confirm New Password"
        type="password"
        autocomplete="new-password"
        required
    />

    <div class="flex items-center gap-4">
        <button type="submit" class="btn-primary">Save</button>

        @if(session('status') === 'password-updated')
            <span class="text-sm text-emerald-700">Saved.</span>
        @endif
    </div>
</form>