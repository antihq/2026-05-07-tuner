<?php

use App\Concerns\ProfileValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit account')] class extends Component
{
    use ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public string $originalEmail = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->originalEmail = $user->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->originalEmail = $this->email;

        Flux::toast(variant: 'success', text: 'Profile updated.');

        $this->redirectRoute('account.show', navigate: true);
    }

    #[Computed]
    public function emailChanged(): bool
    {
        return $this->email !== $this->originalEmail;
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        $user = Auth::user();

        return $user instanceof Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail();
    }
}; ?>

<section class="w-full">
    <div>
        <flux:heading size="xl" level="1">Edit account</flux:heading>
        <form wire:submit="updateProfileInformation" class="mt-6 space-y-8">
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" type="text" required autofocus autocomplete="name" class="max-w-lg" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input wire:model="email" type="email" required autocomplete="email" class="max-w-lg" />
                <flux:error name="email" />
                <flux:description>Each account requires a unique email address.</flux:description>

                @if ($this->emailChanged && Auth::user() instanceof Illuminate\Contracts\Auth\MustVerifyEmail && Auth::user()->hasVerifiedEmail())
                    <flux:text color="amber" class="mt-2">
                        Your email will be marked as unverified.
                    </flux:text>
                @endif
            </flux:field>

            <flux:button variant="primary" type="submit" data-test="update-profile-button">
                Save
            </flux:button>
        </form>
    </div>
</section>
