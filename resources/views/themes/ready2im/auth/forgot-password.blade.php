<x-guest-layout title="🔑 Reset your password">
    <div class="mb-4 text-sm text-slate-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form id="forgot-password-form" method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- Unbotable: off-screen honeypot + signed render-timestamp. --}}
        @unbotableHoneypot
        @unbotableTimestamp

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>{{ __('Email Password Reset Link') }}</x-primary-button>
        </div>
    </form>

    @push('scripts')
        @unbotableWire('#forgot-password-form')
    @endpush
</x-guest-layout>
