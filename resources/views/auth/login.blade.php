<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Correo electrónico -->
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Recuérdame -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900" name="remember">
                <span class="ms-2 text-sm text-slate-600 dark:text-slate-400">{{ __('Recuérdame') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4 flex-col">
            <x-primary-button class="w-full mb-2">
                {{ __('Iniciar sesión') }}
            </x-primary-button>
            <div class="flex gap-2">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-slate-600 hover:text-slate-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 dark:text-slate-400 dark:hover:text-slate-200 dark:focus:ring-offset-slate-900" href="{{ route('register') }}">
                        {{ __('¿Aún no tenés cuenta?') }}
                    </a>
                @endif
                <p class=" text-sm text-slate-600 dark:text-slate-400 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">O</p>
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-slate-600 hover:text-slate-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 dark:text-slate-400 dark:hover:text-slate-200 dark:focus:ring-offset-slate-900" href="{{ route('password.request') }}">
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                @endif
            </div>
        </div>
    </form>
</x-guest-layout>
