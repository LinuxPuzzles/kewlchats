@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-bold text-sm text-green-700']) }}>
        {{ $status }}
    </div>
@endif
