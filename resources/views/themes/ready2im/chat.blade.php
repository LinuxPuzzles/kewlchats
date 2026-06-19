<x-app-layout>
    <x-slot name="header">
        <h2 class="text-white font-extrabold text-xl tracking-tight drop-shadow">
            {{ __('Web chat') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 pt-6 space-y-6">

        <div class="rounded-lg bg-blue-50 border border-blue-300 p-4 text-sm text-blue-900">
            👋 You're in — this is live chat. Say hi and jump in. Public rooms are public — anyone
            here can see what you say.
        </div>

        @if (auth()->user()->xmppIsActive())
            {{-- Shared functional Converse embed (its own client UI). --}}
            <x-converse-chat />
        @else
            <div class="r2-window">
                <div class="r2-winbody p-10 text-center">
                    <div class="text-4xl">⏳</div>
                    <h3 class="mt-3 text-lg font-bold text-slate-900">Setting up your account</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Your chat account isn't active yet. Once it's ready you'll be able to chat right here.
                    </p>
                </div>
            </div>
        @endif

        <p class="text-xs text-slate-600 text-center">
            Prefer your phone? Conversations (Android) and Monal (iPhone) use the same account — set them up from your
            <a href="{{ route('dashboard') }}" class="underline text-blue-700 hover:text-blue-900">dashboard</a>.
        </p>
    </div>
</x-app-layout>
