<x-layouts::auth title="Email verification">
    <section class="max-w-2xl">
        <flux:heading level="1" class="lowercase">verify your email</flux:heading>

        @if (session('status') == 'verification-link-sent')
            <p class="mt-1">A new link has been sent to <x-strong>{{ auth()->user()->email }}</x-strong>.</p>
        @else
            <p class="mt-1">A verification link has been sent to <x-strong>{{ auth()->user()->email }}</x-strong>. Click it to complete registration.</p>
        @endif

        <div class="mt-4 space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" color="lime" class="lowercase">
                    Resend verification email
                </flux:button>
            </form>

            <flux:separator />

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button type="submit" data-test="logout-button" class="lowercase">
                    Sign out
                </flux:button>
            </form>
        </div>
    </section>
</x-layouts::auth>
