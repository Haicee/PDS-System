<x-guest-layout>
    <div class="flex items-center justify-center px-4">
        
        <section class="w-full max-w-xl rounded-3xl border border-white/10 bg-white/60 p-8 shadow-2xl backdrop-blur">
            <!-- Session Status -->
            <x-auth-session-status class="mb-6" :status="session('status')" />

            <div class="mb-4 space-y-2">
                <img src="{{ asset('images/Bfar logo.png') }}" alt="BFAR" class="block mx-auto h-16 w-auto object-contain drop-shadow-md sm:h-18 lg:h-24">
                <h2 class="text-center text-3xl font-semibold text-slate-900">Sign in to BFAR Portal</h2>
                <p class="text-center text-sm text-slate-500">Use your official BFAR email account to continue.</p>
            </div>

            <form class="space-y-6" method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8l7.89 5.26c.68.45 1.54.45 2.22 0L21 8"/><path d="M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/></svg>
                        <input id="email" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@bfar.gov.ph" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="text-sm font-medium text-slate-700">{{ __('Password') }}</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="10" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <input id="password" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" type="password" name="password" required autocomplete="current-password" placeholder="Enter password" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 text-sm">
                    <label for="remember_me" class="inline-flex items-center gap-2 text-slate-600">
                        <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" name="remember">
                        <span>{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="font-medium text-emerald-700 transition hover:text-emerald-600" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <button type="submit" class="group relative inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-500 via-sky-500 to-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-500/30 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-500">
                    <span class="absolute inset-0 rounded-2xl opacity-0 transition group-hover:opacity-20" style="background: linear-gradient(120deg, rgba(255,255,255,.7), rgba(255,255,255,0));"></span>
                    {{ __('Log in') }}
                </button>

                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <p class="font-medium text-slate-800">Don't have an account yet?</p>
                    <p class="mt-1 text-slate-600">
                        It only takes a minute to set up your profile.
                        <br>
                        <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Sign up here →</a>
                    </p>
                </div>
            </form>
        </section>
    </div>
</x-guest-layout>
