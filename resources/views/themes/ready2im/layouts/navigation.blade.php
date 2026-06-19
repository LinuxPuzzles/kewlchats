<nav x-data="{ open: false }" class="r2-nav sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between items-center h-14">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="text-white">
                    <x-brand class="text-lg" />
                </a>
                <div class="hidden sm:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" class="r2-navlink {{ request()->routeIs('dashboard') ? 'r2-navlink--active' : '' }}">Dashboard</a>
                    <a href="{{ route('chat') }}" class="r2-navlink {{ request()->routeIs('chat') ? 'r2-navlink--active' : '' }}">Chat</a>
                    @can('admin')
                        <a href="{{ route('admin.users') }}" class="r2-navlink {{ request()->routeIs('admin.*') ? 'r2-navlink--active' : '' }}">Admin</a>
                    @endcan
                </div>
            </div>

            <div class="hidden sm:flex items-center">
                <x-dropdown align="right" width="48" :contentClasses="'py-1 bg-white'">
                    <x-slot name="trigger">
                        <button class="r2-navlink">{{ Auth::user()->name }} <span aria-hidden="true">▾</span></button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="sm:hidden">
                <button @click="open = ! open" class="r2-navlink text-lg" aria-label="Menu">☰</button>
            </div>
        </div>

        <div x-show="open" x-cloak class="sm:hidden pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block r2-navlink">Dashboard</a>
            <a href="{{ route('chat') }}" class="block r2-navlink">Chat</a>
            @can('admin')
                <a href="{{ route('admin.users') }}" class="block r2-navlink">Admin</a>
            @endcan
            <div class="mt-2 pt-2 border-t border-white/20">
                <div class="px-3 text-xs text-blue-100">{{ Auth::user()->name }} · {{ Auth::user()->email }}</div>
                <a href="{{ route('profile.edit') }}" class="block r2-navlink">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" class="block r2-navlink"
                       onclick="event.preventDefault(); this.closest('form').submit();">Log Out</a>
                </form>
            </div>
        </div>
    </div>
</nav>
