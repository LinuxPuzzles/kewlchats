@props(['tilt' => 'rotate-1'])

{{-- Reusable glassy "chat illustration" frame. Generic on purpose — shows the idea,
     not any specific app. Pass message/call markup as the slot. --}}
<div class="relative">
    <div class="absolute -inset-4 bg-gradient-to-tr from-fuchsia-600/20 to-indigo-600/20 blur-2xl rounded-full pointer-events-none"></div>
    <div {{ $attributes->merge(['class' => "relative mx-auto max-w-sm rounded-3xl bg-white/5 border border-white/10 backdrop-blur p-4 shadow-2xl shadow-fuchsia-500/10 $tilt"]) }}>
        {{ $slot }}
    </div>
</div>
