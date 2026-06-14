<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">{{ __('Admin · Channels') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-admin-nav active="channels" />

            @if (session('status'))
                <div class="rounded-2xl bg-emerald-500/10 border border-emerald-400/20 p-4 text-sm text-emerald-200">{{ session('status') }}</div>
            @endif

            {{-- Create a persistent public channel --}}
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-slate-100">New public channel</h3>
                <p class="text-sm text-slate-400 mt-1">Creates a persistent room that stays around even when empty.</p>
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

            {{-- Existing channels --}}
            <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                @forelse ($channels as $channel)
                    <div class="flex items-center justify-between px-6 py-4 border-b border-white/5 last:border-0">
                        <div>
                            <div class="font-medium text-slate-100">{{ $channel->name }}</div>
                            <div class="text-xs text-slate-400">{{ $channel->description }}</div>
                            <code class="text-xs text-fuchsia-300">{{ $channel->jid() }}</code>
                        </div>
                        <form method="POST" action="{{ route('admin.channels.destroy', $channel) }}"
                              onsubmit="return confirm('Delete #{{ $channel->localpart }}?')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 rounded-lg bg-rose-500/80 hover:bg-rose-500 text-white text-sm transition">Delete</button>
                        </form>
                    </div>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-slate-400">No channels yet — create one above.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
