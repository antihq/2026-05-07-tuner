<x-layouts::guest title="Email verification">
    <section class="w-full">
        <div class="mx-auto max-w-md">
            <flux:heading size="xl" level="1">Verify your email</flux:heading>

            @if (session('status') == 'verification-link-sent')
                <flux:text class="mt-1">
                    A new link has been sent to <x-strong>{{ auth()->user()->email }}</x-strong>.
                </flux:text>
            @else
                <flux:text class="mt-1">A verification link has been sent to <x-strong>{{ auth()->user()->email }}</x-strong>. Click it to complete registration.</flux:text>
            @endif

            <div class="mt-4 space-y-8">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <flux:button type="submit" variant="primary">
                        Resend verification email
                    </flux:button>
                </form>

                <flux:separator />

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:button type="submit" data-test="logout-button">
                        Sign out
                    </flux:button>
                </form>
            </div>
        </div>
    </section>
</x-layouts::guest>
