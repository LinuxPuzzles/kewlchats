<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white/10 border border-white/10 rounded-lg font-semibold text-xs text-slate-200 uppercase tracking-widest hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 focus:ring-offset-2 focus:ring-offset-slate-950 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
