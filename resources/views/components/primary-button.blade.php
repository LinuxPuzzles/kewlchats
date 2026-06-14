<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-fuchsia-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-fuchsia-400 focus:bg-fuchsia-400 active:bg-fuchsia-600 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 focus:ring-offset-2 focus:ring-offset-slate-950 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
