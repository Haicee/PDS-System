<x-app-layout>
    <div class="py-10">
        <div class="mx-auto sm:px-6 lg:px-12 xl:px-16 space-y-10">
            <!-- Greeting + actions -->
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-2xl shadow-lg p-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <p class="text-sm uppercase tracking-wide text-white/80">Employee workspace</p>
                    <h1 class="text-2xl lg:text-3xl font-semibold">Welcome back, {{ auth()->user()->name ?? 'Employee' }}</h1>
                    <p class="text-white/80 text-sm lg:text-base">Manage your Personal Data Sheet, track review status, and upload supporting documents.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="#" class="inline-flex items-center gap-2 rounded-xl bg-white text-sky-700 px-4 py-2.5 text-sm font-semibold shadow-sm hover:shadow-md transition">Start new PDS</a>
                    <a href="#" class="inline-flex items-center gap-2 rounded-xl border border-white/60 text-white px-4 py-2.5 text-sm font-semibold hover:bg-white/10 transition">Upload documents</a>
                </div>
            </div>

            <!-- Overview cards -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($stats['overview'] as $item)
                    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm p-5">
                        <div class="text-sm font-semibold text-slate-500">{{ $item['label'] }}</div>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $item['value'] }}</p>
                        <div class="mt-4 h-1.5 rounded-full bg-slate-100">
                            <div class="h-1.5 w-2/3 rounded-full bg-gradient-to-r {{ $item['accent'] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Quick links and reminders -->
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 bg-white border border-slate-100 shadow-sm rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-base font-semibold text-slate-900">Quick actions</p>
                            <p class="text-sm text-slate-500">Jump to common tasks</p>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-3 gap-4">
                        @foreach ($stats['quickLinks'] as $link)
                            <a href="{{ $link['href'] }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:border-sky-400 hover:shadow-sm transition">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                                    @if($link['icon'] === 'file-plus')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" /></svg>
                                    @elseif($link['icon'] === 'upload')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 17v2a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-2M16 11l-4-4m0 0-4 4m4-4v12"/></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    @endif
                                </span>
                                <span class="text-sm font-semibold text-slate-900">{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-100 via-white to-white border border-amber-200/70 rounded-2xl p-6 shadow-sm space-y-3">
                    <p class="text-sm font-semibold text-amber-700">Reminders</p>
                    <ul class="space-y-2 text-sm text-amber-900">
                        <li class="flex gap-2">
                            <span class="mt-0.5 h-2 w-2 rounded-full bg-amber-500"></span>
                            Keep your contact info up to date for HR notices.
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-0.5 h-2 w-2 rounded-full bg-amber-500"></span>
                            Upload recent certifications as supporting documents.
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-0.5 h-2 w-2 rounded-full bg-amber-500"></span>
                            Track returned submissions and resubmit promptly.
                        </li>
                    </ul>
                </div>
            </div>
</x-app-layout>
