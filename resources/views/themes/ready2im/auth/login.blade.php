<x-guest-layout title="🔑 Sign On">
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form id="login-form" method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Unbotable: off-screen honeypot + signed render-timestamp. --}}
        @unbotableHoneypot
        @unbotableTimestamp

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-400 text-blue-600 focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-blue-700 hover:text-blue-900" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">{{ __('Sign On') }}</x-primary-button>
        </div>

        <p class="mt-5 text-center text-sm text-slate-500">
            New here? <a href="{{ route('register') }}" class="text-blue-700 underline">Get a name</a>
        </p>
    </form>

    @push('scripts')
        @unbotableWire('#login-form')
    @endpush
</x-guest-layout>
