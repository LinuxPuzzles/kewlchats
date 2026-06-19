<button {{ $attributes->merge(['type' => 'submit', 'class' => 'r2-btn r2-btn--primary']) }}>
    {{ $slot }}
</button>
