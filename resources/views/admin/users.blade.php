<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">{{ __('Admin · Users') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-admin-nav active="users" />

            {{-- Live stats: online from ejabberd, the rest from the DB --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                    <div class="text-2xl font-bold text-emerald-400">{{ number_format($stats['online']) }}</div>
                    <div class="text-xs text-slate-400 mt-1">online now</div>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                    <div class="text-2xl font-bold text-slate-100">{{ number_format($stats['registered']) }}</div>
                    <div class="text-xs text-slate-400 mt-1">registered</div>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                    <div class="text-2xl font-bold {{ $stats['banned'] ? 'text-rose-400' : 'text-slate-100' }}">{{ number_format($stats['banned']) }}</div>
                    <div class="text-xs text-slate-400 mt-1">banned</div>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                    <div class="text-2xl font-bold text-slate-100">{{ number_format($stats['channels']) }}</div>
                    <div class="text-xs text-slate-400 mt-1">channels</div>
                </div>
            </div>

            @if (session('status'))
                <div class="rounded-2xl bg-emerald-500/10 border border-emerald-400/20 p-4 text-sm text-emerald-200">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-2xl bg-rose-500/10 border border-rose-400/20 p-4 text-sm text-rose-200">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white/5 border border-white/10 rounded-2xl overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-slate-400 text-left border-b border-white/10">
                        <tr>
                            <th class="px-4 py-3 font-medium">User</th>
                            <th class="px-4 py-3 font-medium">Chat address</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($users as $user)
                            <tr class="text-slate-200">
                                <td class="px-4 py-3">
                                    <div class="font-medium">
                                        {{ $user->name }}
                                        @if ($user->is_admin)<span class="ml-1 text-xs text-fuchsia-300">· admin</span>@endif
                                    </div>
                                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-fuchsia-300">{{ $user->jid() ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($user->isBanned())
                                        <span class="text-rose-400">Banned</span>
                                        @if ($user->ban_reason)<div class="text-xs text-slate-500">{{ $user->ban_reason }}</div>@endif
                                    @else
                                        <span class="text-slate-300">{{ ucfirst($user->xmpp_status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                                    @if ($user->isBanned())
                                        <form method="POST" action="{{ route('admin.users.unban', $user) }}" class="inline">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-slate-200 transition">Unban</button>
                                        </form>
                                    @else
                                        @unless ($user->is_admin)
                                            <form method="POST" action="{{ route('admin.users.kick', $user) }}" class="inline"
                                                  onsubmit="return confirm('Disconnect {{ $user->email }}’s live sessions?')">
                                                @csrf
                                                <button class="px-3 py-1.5 rounded-lg bg-amber-500/80 hover:bg-amber-500 text-white transition">Kick</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="inline"
                                                  onsubmit="return confirm('Ban {{ $user->email }}? This blocks their login and chat.')">
                                                @csrf
                                                <button class="px-3 py-1.5 rounded-lg bg-rose-500/80 hover:bg-rose-500 text-white transition">Ban</button>
                                            </form>
                                        @endunless
                                        <form method="POST" action="{{ route('admin.users.reset', $user) }}" class="inline"
                                              onsubmit="return confirm('Email a password-reset link to {{ $user->email }}?')">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-slate-200 transition">Send reset</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
