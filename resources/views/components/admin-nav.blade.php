@props(['active' => ''])

<div class="flex gap-2 text-sm">
    <a href="{{ route('admin.users') }}"
       class="px-4 py-2 rounded-lg {{ $active === 'users' ? 'bg-fuchsia-500 text-white' : 'bg-white/5 text-slate-300 hover:bg-white/10' }} transition">Users</a>
    <a href="{{ route('admin.channels') }}"
       class="px-4 py-2 rounded-lg {{ $active === 'channels' ? 'bg-fuchsia-500 text-white' : 'bg-white/5 text-slate-300 hover:bg-white/10' }} transition">Channels</a>
</div>
