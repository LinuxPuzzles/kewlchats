@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-white/10 bg-white/5 text-slate-100 placeholder-slate-500 shadow-sm focus:border-fuchsia-400 focus:ring-fuchsia-400']) }}>
