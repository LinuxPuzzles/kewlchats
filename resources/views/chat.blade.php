<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">
            {{ __('Web chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Friendly welcome. (No retention claims here — rooms carry history now.) --}}
            <div class="rounded-2xl bg-fuchsia-500/10 border border-fuchsia-400/20 p-5 text-sm">
                <p class="font-semibold text-fuchsia-100">👋 You’re in — this is live chat.</p>
                <p class="mt-1 text-fuchsia-100/80">
                    Say hi and jump into the conversation. Public rooms are public — anyone here can
                    see what you say.
                </p>
            </div>

            @if (auth()->user()->xmppIsActive())
                {{-- Functional Converse embed lives in a shared component so themes can
                     restyle the chrome above/below without duplicating the chat JS. --}}
                <x-converse-chat />
            @else
                <div class="bg-white/5 border border-white/10 rounded-2xl min-h-[320px] flex items-center justify-center p-10 text-center">
                    <div class="max-w-sm">
                        <div class="text-4xl">⏳</div>
                        <h3 class="mt-3 text-lg font-semibold text-slate-100">Setting up your account</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            Your chat account isn’t active yet. Once it’s ready you’ll be able to chat right here.
                        </p>
                    </div>
                </div>
            @endif

            <p class="text-xs text-slate-500 text-center">
                Prefer your phone? Conversations (Android) and Monal (iPhone) use the same account — set them up from your
                <a href="{{ route('dashboard') }}" class="underline hover:text-slate-300">dashboard</a>.
            </p>
        </div>
    </div>
</x-app-layout>
