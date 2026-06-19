<x-app-layout>
    <x-slot name="header">
        <h2 class="text-white font-extrabold text-xl tracking-tight drop-shadow">{{ __('Admin · Users') }}</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 pt-6 space-y-6">
        <x-admin-nav active="users" />

        {{-- Live stats: online from ejabberd, the rest from the DB --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="r2-window"><div class="r2-winbody text-center">
                <div class="text-2xl font-extrabold text-green-700">{{ number_format($stats['online']) }}</div>
                <div class="text-xs text-slate-500 mt-1">online now</div>
            </div></div>
            <div class="r2-window"><div class="r2-winbody text-center">
                <div class="text-2xl font-extrabold text-slate-900">{{ number_format($stats['registered']) }}</div>
                <div class="text-xs text-slate-500 mt-1">registered</div>
            </div></div>
            <div class="r2-window"><div class="r2-winbody text-center">
                <div class="text-2xl font-extrabold {{ $stats['banned'] ? 'text-red-600' : 'text-slate-900' }}">{{ number_format($stats['banned']) }}</div>
                <div class="text-xs text-slate-500 mt-1">banned</div>
            </div></div>
            <div class="r2-window"><div class="r2-winbody text-center">
                <div class="text-2xl font-extrabold text-slate-900">{{ number_format($stats['channels']) }}</div>
                <div class="text-xs text-slate-500 mt-1">channels</div>
            </div></div>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-green-100 border border-green-300 p-4 text-sm text-green-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg bg-red-100 border border-red-300 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <div class="r2-window">
            <div class="r2-titlebar">
                <span>👥 Users</span>
                <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
            </div>
            <div class="r2-winbody p-0 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-slate-500 text-left border-b border-slate-300">
                        <tr>
                            <th class="px-4 py-3 font-bold">User</th>
                            <th class="px-4 py-3 font-bold">Chat address</th>
                            <th class="px-4 py-3 font-bold">Status</th>
                            <th class="px-4 py-3 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($users as $user)
                            <tr class="text-slate-800">
                                <td class="px-4 py-3">
                                    <div class="font-bold">
                                        {{ $user->name }}
                                        @if ($user->is_admin)<span class="ml-1 text-xs text-blue-700">· admin</span>@endif
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-blue-700">{{ $user->jid() ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($user->isBanned())
                                        <span class="text-red-600 font-medium">Banned</span>
                                        @if ($user->ban_reason)<div class="text-xs text-slate-500">{{ $user->ban_reason }}</div>@endif
                                    @else
                                        <span class="text-slate-600">{{ ucfirst($user->xmpp_status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                                    @if ($user->isBanned())
                                        <form method="POST" action="{{ route('admin.users.unban', $user) }}" class="inline">
                                            @csrf
                                            <button class="px-3 py-1 rounded border border-slate-300 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs transition">Unban</button>
                                        </form>
                                    @else
                                        @unless ($user->is_admin)
                                            <form method="POST" action="{{ route('admin.users.kick', $user) }}" class="inline"
                                                  onsubmit="return confirm('Disconnect {{ $user->email }}’s live sessions?')">
                                                @csrf
                                                <button class="px-3 py-1 rounded bg-amber-500 hover:bg-amber-600 text-white text-xs transition">Kick</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="inline"
                                                  onsubmit="return confirm('Ban {{ $user->email }}? This blocks their login and chat.')">
                                                @csrf
                                                <button class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs transition">Ban</button>
                                            </form>
                                        @endunless
                                        <form method="POST" action="{{ route('admin.users.reset', $user) }}" class="inline"
                                              onsubmit="return confirm('Email a password-reset link to {{ $user->email }}?')">
                                            @csrf
                                            <button class="px-3 py-1 rounded border border-slate-300 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs transition">Send reset</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-white">{{ $users->links() }}</div>
    </div>
</x-app-layout>
