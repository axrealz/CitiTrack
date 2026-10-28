<form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf
    @method('PATCH')

    <x-form-input
        name="name"
        label="Name"
        :value="old('name', $user->name)"
        required
        autofocus
    />

    <x-form-input
        name="email"
        label="Email"
        type="email"
        :value="old('email', $user->email)"
        required
    />

    @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="p-3 bg-amber-50 border border-amber-200 rounded text-sm">
            <p class="text-amber-800">
                Your email address is unverified.
                <button form="send-verification" class="underline text-amber-900 hover:text-amber-700">
                    Click here to re-send the verification email.
                </button>
            </p>

            @if(session('status') === 'verification-link-sent')
                <p class="text-emerald-700 mt-2 text-xs">
                    A new verification link has been sent to your email.
                </p>
            @endif
        </div>
    @endif

    <div class="flex items-center gap-4">
        <button type="submit" class="btn-primary">Save</button>

        @if(session('status') === 'profile-updated')
            <span class="text-sm text-emerald-700">Saved.</span>
        @endif
    </div>
</form>

<form id="send-verification" method="POST" action="{{ route('verification.send') }}" class="hidden">
    @csrf
</form>