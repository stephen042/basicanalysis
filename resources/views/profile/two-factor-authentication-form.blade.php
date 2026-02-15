<div class="p-6 rounded-lg bg-dark-200 text-gray-300">
    <h2 class="text-xl font-bold text-white">
        {{ __('Two Factor Authentication') }}
    </h2>

    <h3 class="mt-4 text-md">
        @if ($this->enabled)
            <span class="px-3 py-1 text-sm font-semibold text-green-800 bg-green-200 rounded-full">Enabled</span>
            {{ __('You have enabled two factor authentication.') }}
        @else
            <span class="px-3 py-1 text-sm font-semibold text-red-800 bg-red-200 rounded-full">Disabled</span>
            {{ __('You have not enabled two factor authentication.') }}
        @endif
    </h3>

    <div class="mt-4 text-sm">
        <p>
            {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
        </p>
    </div>

    @if ($this->enabled)
        @if ($showingQrCode)
            <div class="mt-4 text-sm">
                <p class="font-semibold text-white">
                    {{ __('Two factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application.') }}
                </p>
            </div>

            <div class="p-4 mt-4 rounded-lg bg-white w-max">
                {!! $this->user->twoFactorQrCodeSvg() !!}
            </div>
        @endif

        @if ($showingRecoveryCodes)
            <div class="mt-4 text-sm">
                <p class="font-semibold text-white">
                    {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                </p>
            </div>

            <div class="grid max-w-xl gap-2 p-4 mt-4 font-mono text-sm rounded-lg bg-dark-300">
                @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                    <div class="p-2 rounded bg-dark-100">{{ $code }}</div>
                @endforeach
            </div>
        @endif
    @endif

    <div class="flex items-center mt-6 space-x-4">
        @if (! $this->enabled)
            <x-jet-confirms-password wire:then="enableTwoFactorAuthentication">
                <button type="button" class="px-6 py-2.5 font-semibold text-center text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700" wire:loading.attr="disabled">
                    {{ __('Enable') }}
                </button>
            </x-jet-confirms-password>
        @else
            @if ($showingRecoveryCodes)
                <x-jet-confirms-password wire:then="regenerateRecoveryCodes">
                    <button class="px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-dark-300 hover:bg-dark-100">
                        {{ __('Regenerate Recovery Codes') }}
                    </button>
                </x-jet-confirms-password>
            @else
                <x-jet-confirms-password wire:then="showRecoveryCodes">
                    <button class="px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-dark-300 hover:bg-dark-100">
                        {{ __('Show Recovery Codes') }}
                    </button>
                </x-jet-confirms-password>
            @endif

            <x-jet-confirms-password wire:then="disableTwoFactorAuthentication">
                <button class="px-4 py-2 font-semibold text-white transition-colors duration-200 bg-red-600 rounded-lg hover:bg-red-700" wire:loading.attr="disabled">
                    {{ __('Disable') }}
                </button>
            </x-jet-confirms-password>
        @endif
    </div>
</div>
