<x-app-layout>
    <x-slot name="header">
        <h2 class="text-white font-extrabold text-xl tracking-tight drop-shadow">{{ __('Admin · Channels') }}</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 pt-6 space-y-6">
        <x-admin-nav active="channels" />

        @if (session('status'))
            <div class="rounded-lg bg-green-100 border border-green-300 p-4 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        {{-- Create a persistent public channel --}}
        <section class="r2-window">
            <div class="r2-titlebar">
                <span>➕ New public channel</span>
                <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
            </div>
            <div class="r2-winbody p-6">
                <p class="text-sm text-slate-600">Creates a persistent room that stays around even when empty.</p>
                <form method="POST" action="{{ route('admin.channels.store') }}" class="mt-4 grid sm:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <x-input-label for="localpart" value="Address (lowercase, no spaces)" />
                        <x-text-input id="localpart" name="localpart" class="mt-1 block w-full" :value="old('localpart')" required />
                        <p class="mt-1 text-xs text-slate-500">{{ 'name@'.config('xmpp.muc_domain') }}</p>
                        <x-input-error :messages="$errors->get('localpart')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="name" value="Display name" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="description" value="Description" />
                        <x-text-input id="description" name="description" class="mt-1 block w-full" :value="old('description')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-primary-button>Create channel</x-primary-button>
                    </div>
                </form>
            </div>
        </section>

        {{-- Existing channels --}}
        <section class="r2-window">
            <div class="r2-titlebar">
                <span>📂 Channels</span>
                <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
            </div>
            <div class="r2-winbody p-0">
                @forelse ($channels as $channel)
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 last:border-0">
                        <div>
                            <div class="font-bold text-slate-900">{{ $channel->name }}</div>
                            <div class="text-xs text-slate-500">{{ $channel->description }}</div>
                            <code class="text-xs text-blue-700">{{ $channel->jid() }}</code>
                        </div>
                        <form method="POST" action="{{ route('admin.channels.destroy', $channel) }}"
                              onsubmit="return confirm('Delete #{{ $channel->localpart }}?')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-sm transition">Delete</button>
                        </form>
                    </div>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-slate-500">No channels yet — create one above.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
