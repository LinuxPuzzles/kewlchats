@props(['active' => ''])

<div class="flex gap-2 text-sm">
    <a href="{{ route('admin.users') }}" class="r2-btn {{ $active === 'users' ? 'r2-btn--primary' : '' }}">Users</a>
    <a href="{{ route('admin.channels') }}" class="r2-btn {{ $active === 'channels' ? 'r2-btn--primary' : '' }}">Channels</a>
</div>
