<x-guest-layout title="✨ Get a name">
    <form id="register-form" method="POST" action="{{ route('register') }}" x-data="{ username: @js(old('username', '')), domain: @js(config('xmpp.domain')) }">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="username" :value="__('Choose your screen name')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username"
                          x-model="username"
                          @input="username = username.toLowerCase()"
                          :value="old('username')" required
                          autocapitalize="none" autocomplete="off"
                          placeholder="yourname" />
            <p class="mt-1 text-sm text-slate-600">
                Your chat address will be
                <span class="font-semibold text-blue-700"
                      x-text="(username || 'yourname') + '@' + domain">yourname{{ '@'.config('xmpp.domain') }}</span>.
                <span class="text-slate-500">Permanent — it can't be changed later.</span>
            </p>
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <p class="mt-1 text-sm text-slate-600">You'll use this same password to sign in to your chat app.</p>
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Unbotable: off-screen honeypot + signed render-timestamp. --}}
        @unbotableHoneypot
        @unbotableTimestamp

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-blue-700 hover:text-blue-900" href="{{ route('login') }}">
                {{ __('Already have a name?') }}
            </a>

            <x-primary-button class="ms-4">{{ __('Get a name') }}</x-primary-button>
        </div>
    </form>

    @push('scripts')
        @unbotableWire('#register-form')
    @endpush
</x-guest-layout>
