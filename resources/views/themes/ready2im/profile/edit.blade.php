<x-app-layout>
    <x-slot name="header">
        <h2 class="text-white font-extrabold text-xl tracking-tight drop-shadow">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 pt-6 space-y-6">

        <section class="r2-window">
            <div class="r2-titlebar">
                <span>⚙️ Profile information</span>
                <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
            </div>
            <div class="r2-winbody p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </section>

        <section class="r2-window">
            <div class="r2-titlebar">
                <span>🔑 Update password</span>
                <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
            </div>
            <div class="r2-winbody p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </section>

        <section class="r2-window">
            <div class="r2-titlebar">
                <span>🗑️ Delete account</span>
                <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
            </div>
            <div class="r2-winbody p-6">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </section>

    </div>
</x-app-layout>
