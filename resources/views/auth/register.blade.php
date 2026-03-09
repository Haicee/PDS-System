<x-guest-layout>
    @vite(['resources/js/app.js', 'resources/js/register-camera.js'])
    <div class="flex items-center justify-center px-4">
        <section class="w-full max-w-3xl rounded-3xl border border-white/10 bg-white/60 p-8 shadow-2xl backdrop-blur">
            <div class="mb-8 space-y-2">
                <img src="{{ asset('images/Bfar logo.png') }}" alt="BFAR" class="block mx-auto h-16 w-auto object-contain drop-shadow-md sm:h-18 lg:h-24">
                <h2 class="text-3xl font-semibold text-slate-900 text-center">Sign up for BFAR Portal</h2>
                <p class="text-sm text-slate-500 text-center">Fill in your details to get started.</p>
            </div>

            <form method="POST" 
                action="{{ route('register', [], false) }}" 
                class="space-y-6"
                enctype="multipart/form-data"
                x-data="formCache()"
                @submit="submitting = true">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="text-sm font-medium text-slate-700">{{ __('Full name') }}</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                        <input id="name" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0 uppercase" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Juan Dela Cruz" oninput="this.value = this.value.toUpperCase();" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Email Address -->
                    <div>
                        <label for="email" class="text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                        <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                            <input id="email" 
                            class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="name@bfar.gov.ph" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    
                    <!-- Phone -->
                    <div>
                        <label for="phone" class="text-sm font-medium text-slate-700">Phone</label>
                        <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone-icon lucide-phone"><path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/></svg>
                            <input id="phone" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="09123456789" required maxlength="11" pattern="\d{11}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);" />
                        </div>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Type -->
                    <div>
                        <label for="type" class="text-sm font-medium text-slate-700">Type</label>
                        <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-search-icon lucide-user-round-search"><circle cx="10" cy="8" r="5"/><path d="M2 21a8 8 0 0 1 10.434-7.62"/><circle cx="18" cy="18" r="3"/><path d="m22 22-1.9-1.9"/></svg>  
                            <select id="type" name="type" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 focus:ring-0" required>
                                <option value="Permanent Employee" {{ old('type', 'Permanent Employee') === 'Permanent Employee' ? 'selected' : '' }}>Permanent Employee</option>
                                <option value="Job Order" {{ old('type') === 'Job Order' ? 'selected' : '' }}>Job Order</option>
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="text-sm font-medium text-slate-700">Gender</label>
                        <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                            <select id="gender" name="gender" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 focus:ring-0" required>
                                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="text-sm font-medium text-slate-700">{{ __('Password') }}</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock-icon lucide-lock"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input id="password" 
                        class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" type="password" name="password" required autocomplete="new-password" placeholder="Create a password" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="text-sm font-medium text-slate-700">{{ __('Confirm Password') }}</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock-icon lucide-lock"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input id="password_confirmation" 
                        class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" 
                        type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter password" />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                    <!-- Unit -->
                    <div>
                        <label for="unit" class="text-sm font-medium text-slate-700">Unit/Division/Section</label>
                        <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2-icon lucide-building-2"><path d="M10 12h4"/><path d="M10 8h4"/><path d="M14 21v-3a2 2 0 0 0-4 0v3"/><path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"/><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/></svg>
                            <select id="unit" name="unit" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" required>
                                <option value="" disabled {{ old('unit') ? '' : 'selected' }}>Select unit</option>
                                @foreach (config('units.list', []) as $unit)
                                    <option value="{{ $unit }}" {{ old('unit') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                    </div>


                <!-- Location Assigned -->
                <div>
                    <label for="location_assigned" class="text-sm font-medium text-slate-700">Location Assigned</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pinned-icon lucide-map-pinned"><path d="M18 8c0 3.613-3.869 7.429-5.393 8.795a1 1 0 0 1-1.214 0C9.87 15.429 6 11.613 6 8a6 6 0 0 1 12 0"/><circle cx="12" cy="8" r="2"/><path d="M8.714 14h-3.71a1 1 0 0 0-.948.683l-2.004 6A1 1 0 0 0 3 22h18a1 1 0 0 0 .948-1.316l-2-6a1 1 0 0 0-.949-.684h-3.712"/></svg>
                        <input id="location_assigned" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0 uppercase" type="text" name="location_assigned" value="{{ old('location_assigned') }}" placeholder="e.g., BFAR Regional HQ – Lagao, GenSan" required oninput="this.value = this.value.toUpperCase();" />
                    </div>
                    <x-input-error :messages="$errors->get('location_assigned')" class="mt-2" />
                </div>

                <!-- Profile Photo -->
                <div>
                    <label class="text-sm font-medium text-slate-700" for="profile_photo">Profile Photo</label>

                    <div class="flex flex-col items-center gap-3">
                        <div class="relative w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 lg:w-64 lg:h-64 rounded-full overflow-hidden border border-gray-200 shadow-sm bg-white">
                            <!-- Live Camera -->
                            <video x-ref="video" class="absolute inset-0 h-full w-full object-cover" 
                                x-show="streaming" playsinline muted></video>

                            <!-- Progress ring on outer border -->
                            <div x-show="streaming"
                                class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <svg class="absolute inset-[-6px] w-[calc(100%+12px)] h-[calc(100%+12px)]" viewBox="0 0 112 112" aria-hidden="true">
                                    <circle cx="56" cy="56" r="52" stroke="rgba(255,255,255,0.25)" stroke-width="6" fill="none" />
                                    <circle cx="56" cy="56" r="52" stroke="#38bdf8" stroke-width="6" fill="none"
                                        stroke-linecap="round"
                                        stroke-dasharray="326.72"
                                        :stroke-dashoffset="`${326.72 * (1 - detectionProgress/100)}`"
                                        transform="rotate(-90 56 56)" />
                                </svg>
                                <div class="relative w-24 h-32 sm:w-32 sm:h-40 md:w-36 md:h-44">
                                    <div class="absolute inset-[6px] rounded-full bg-transparent border-4 border-white/70 border-dashed"></div>
                                </div>
                            </div>

                            <!-- Image Preview -->
                            <template x-if="preview">
                                <img :src="preview" alt="Profile preview" 
                                    class="h-full w-full object-cover" />
                            </template>

                            <!-- Placeholder -->
                            <template x-if="!preview && !streaming">
                                <div class="flex h-full w-full items-center justify-center text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-20 h-20 sm:w-28 sm:h-28 md:w-36 md:h-36 lg:w-44 lg:h-44"
                                        viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                            </template>

                        </div>

                        <p x-text="detectionMessage"
                               x-show="detectionMessage"
                               class="text-xs font-semibold text-rose-600 text-center px-4"></p>

                        <div class="flex flex-col items-center gap-2">
                            <input 
                                x-ref="uploadInput"
                                id="profile_photo_upload"
                                name="profile_photo"
                                type="file"
                                accept="image/*"
                                required
                                class="hidden"
                                @change="handlePhoto($event)">

                            <canvas x-ref="canvas" class="hidden"></canvas>

                            <div class="flex flex-wrap items-center justify-center gap-3">
                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-emerald-400 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300" @click="startCamera()" x-show="!streaming">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 8h.01"/><path d="M17 6h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h2"/><path d="m3 10 2.586-2.586a2 2 0 0 1 2.828 0L12 11l2.586-2.586a2 2 0 0 1 2.828 0L21 11"/><circle cx="12" cy="13" r="3"/></svg>
                                    Take Photo
                                </button>   
                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-emerald-400 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300" @click="chooseUpload()" x-show="!streaming">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="m19 12-7-7-7 7"/><path d="M5 19h14"/></svg>
                                    Upload Photo
                                </button>
                            </div>

                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-transparent px-4 py-2 text-xs font-semibold text-slate-500 underline decoration-dashed decoration-slate-400 transition hover:text-rose-600" @click="clear()" x-show="preview || streaming">
                                Remove photo / stop camera
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">Use your device camera or upload a clear headshot. Square/circle framing shows how it will display.</p>
                    <x-input-error :messages="$errors->get('profile_photo')" class="mt-1" />
                </div>

                <button type="submit" 
                    class="group relative inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-500 via-sky-500 to-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-500/30 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-70"
                    :disabled="submitting">
                    <span class="absolute inset-0 rounded-2xl opacity-0 transition group-hover:opacity-20" style="background: linear-gradient(120deg, rgba(255,255,255,.7), rgba(255,255,255,0));"></span>
                    {{ __('Create account') }}
                </button>

                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <p class="font-medium text-slate-800">Have an account already?</p>
                    <p class="mt-1 text-slate-600">
                        Log in with your BFAR account.
                        <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Go to login →</a>
                    </p>
                </div>
            </form>
        </section>
    </div>

  

@if ($errors->any())
<script>
document.addEventListener("DOMContentLoaded", () => {

    let cache = JSON.parse(localStorage.getItem("register_cache") || "{}");

    @foreach ($errors->keys() as $field)
        delete cache["{{ $field }}"];
    @endforeach

    localStorage.setItem("register_cache", JSON.stringify(cache));

});
</script>
@endif

@if(session('clearRegisterCache'))
<script>
localStorage.removeItem("register_cache");
</script>
@endif
</x-guest-layout>


