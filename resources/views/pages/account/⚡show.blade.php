<?php

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Account')] class extends Component
{
    #[Computed]
    public function userName(): string
    {
        return Auth::user()->name;
    }

    #[Computed]
    public function userEmail(): string
    {
        return Auth::user()->email;
    }

    #[Computed]
    public function emailVerifiedStatus(): string
    {
        $user = Auth::user();

        if ($user instanceof MustVerifyEmail) {
            return $user->hasVerifiedEmail()
                ? 'Verified on ' . $user->email_verified_at->format('M j, Y')
                : 'Not verified';
        }

        return '—';
    }

    #[Computed]
    public function emailVerificationEnabled(): bool
    {
        return Auth::user() instanceof MustVerifyEmail;
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function activeSessionsCount(): int
    {
        return Auth::user()->activeSessionsCount();
    }

    #[Computed]
    public function registeredAt(): string
    {
        return Auth::user()->created_at->format('M j, Y');
    }

    #[Computed]
    public function twoFactorStatus(): string
    {
        $user = Auth::user();

        if (! Features::canManageTwoFactorAuthentication()) {
            return 'Not available';
        }

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_confirmed_at) {
            return 'Enabled on ' . $user->two_factor_confirmed_at->format('M j, Y');
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return 'Enabled';
        }

        return 'Disabled';
    }

    #[Computed]
    public function teamCount(): int
    {
        return Auth::user()->teams()->count();
    }

    #[Computed]
    public function currentTeamName(): ?string
    {
        return Auth::user()->currentTeam?->name;
    }

    #[Computed]
    public function currentTeamSlug(): ?string
    {
        return Auth::user()->currentTeam?->slug;
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Flux::toast(text: 'A new verification link has been sent to your email address.');
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <flux:heading size="xl" level="1">Account</flux:heading>
        <flux:button variant="primary" :href="route('account.edit')" wire:navigate data-test="account-edit-button">
            Edit
        </flux:button>
    </div>

    <x-description.list class="mt-2.5">
        <x-description.term>Name</x-description.term>
        <x-description.details>{{ $this->userName }}</x-description.details>

        @if ($this->emailVerificationEnabled)
            <x-description.term>Email</x-description.term>
            <x-description.details>
                <div class="flex items-center gap-x-4 gap-y-2 flex-wrap">
                    {{ $this->emailVerifiedStatus }}
                    @if ($this->hasUnverifiedEmail)
                        <flux:button wire:click="resendVerificationNotification">
                            Resend verification email
                        </flux:button>
                    @endif
                </div>
            </x-description.details>
        @endif

        <x-description.term>Registered</x-description.term>
        <x-description.details>{{ $this->registeredAt }}</x-description.details>

        <x-description.term>Active sessions</x-description.term>
        <x-description.details>{{ $this->activeSessionsCount }} active</x-description.details>

        <x-description.term>Two-factor</x-description.term>
        <x-description.details>
            <flux:link :accent="false" :href="route('authenticator.show')" wire:navigate>
                {{ $this->twoFactorStatus }}
            </flux:link>
        </x-description.details>

        <x-description.term>Teams</x-description.term>
        <x-description.details>
            <flux:link :accent="false" :href="route('teams.index')" wire:navigate>
                {{ $this->teamCount }} {{ str()->plural('team', $this->teamCount) }}
            </flux:link>
        </x-description.details>

        <x-description.term>Current team</x-description.term>
        <x-description.details>
            @if ($this->currentTeamName)
                <flux:link :accent="false" :href="route('teams.show', $this->currentTeamSlug)" wire:navigate>
                    {{ $this->currentTeamName }}
                </flux:link>
            @else
                —
            @endif
        </x-description.details>
    </x-description.list>

    <flux:separator class="mt-8" />

    <div class="mt-4">
        <flux:button variant="danger" :href="route('account.delete')" wire:navigate data-test="account-delete-button">
            Delete account
        </flux:button>
    </div>
</section>
