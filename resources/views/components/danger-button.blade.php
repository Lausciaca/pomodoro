<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn bg-brand-600 text-white shadow-sm hover:bg-brand-500 active:bg-brand-700 focus-visible:ring-brand-500']) }}>
    {{ $slot }}
</button>
