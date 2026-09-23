@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg bg-brand-50 py-2 ps-3 pe-4 text-start text-base font-semibold text-brand-700 focus:outline-none focus:bg-brand-100 transition duration-150 ease-in-out dark:bg-brand-500/10 dark:text-brand-300 dark:focus:bg-brand-500/20'
            : 'block w-full rounded-lg py-2 ps-3 pe-4 text-start text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:bg-slate-50 transition duration-150 ease-in-out dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100 dark:focus:bg-slate-800';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
