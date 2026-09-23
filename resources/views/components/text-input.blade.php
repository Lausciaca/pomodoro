@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-brand-500 focus:ring-brand-500 sm:text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500']) }}>
