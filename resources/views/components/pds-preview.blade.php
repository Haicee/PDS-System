<div
    {{ $attributes->merge(['class' => 'fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-3 sm:px-6 py-4']) }}
    x-cloak
    @keydown.escape.window="$dispatch('close')"
    @click.self="$dispatch('close')"
>
    <div
        class="
            bg-white
            w-full
            h-full
            max-w-[95vw]
            max-h-[95vh]
            sm:max-w-10xl
            sm:max-h-[90vh]
            rounded-none
            sm:rounded-2xl
            shadow-2xl
            overflow-hidden
            flex
            flex-col
        "
    >

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class=" uppercase font-semibold text-indigo-500">PDS Submission</h2>
            </div>
            <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100" @click="$dispatch('close')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6m0 12L6 6" />
                </svg>
            </button>
        </div>
        
    <div class="flex-1 overflow-hidden">
        <div class="grid gap-6 px-6 py-6 h-full lg:grid-cols-[280px_minmax(0,1fr)]">
            <div class="space-y-4 pr-2 lg:pr-0">
                <div class="flex items-center gap-3">
                    <img :src="selected?.avatar" :alt="(selected?.name ?? 'User') + ' avatar'" class="h-12 w-12 rounded-full object-cover shadow" />
                    <div>
                        <p class="text-base font-semibold text-slate-900" x-text="selected?.name ?? '—'"></p>
                        <p class="text-sm text-slate-500" x-text="selected?.type ?? '—'"></p>
                    </div>
                </div>

                <div class="grid gap-6 text-sm text-slate-700">
                    <div>
                        <p class="text-xs uppercase text-slate-400 font-semibold">Email</p>
                        <p class="font-medium" x-text="selected?.email ?? '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-slate-400 font-semibold">Unit</p>
                        <p class="font-medium" x-text="selected?.unit ?? '—'"></p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <p class="text-xs uppercase text-slate-400 font-semibold">Status</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button"
                                class="rounded-full px-3 py-1.5 text-xs font-semibold border"
                                :class="(selected?.status_key ?? 'pending') === 'pending'
                                    ? 'border-amber-200 bg-amber-50 text-amber-700 shadow-sm'
                                    : 'border-slate-200 text-slate-600 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700'"
                                @click.stop="requestConfirm('pending')">
                                Pending
                            </button>
                            <button type="button"
                                class="rounded-full px-3 py-1.5 text-xs font-semibold border"
                                :class="(selected?.status_key ?? 'pending') === 'approved'
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700 shadow-sm'
                                    : 'border-slate-200 text-slate-600 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700'"
                                @click.stop="requestConfirm('approved')">
                                Approved
                            </button>
                            <button type="button"
                                class="rounded-full px-3 py-1.5 text-xs font-semibold border"
                                :class="(selected?.status_key ?? 'pending') === 'rejected'
                                    ? 'border-rose-200 bg-rose-50 text-rose-700 shadow-sm'
                                    : 'border-slate-200 text-slate-600 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700'"
                                @click.stop="requestConfirm('rejected')">
                                Rejected
                            </button>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-slate-400 font-semibold">Submitted</p>
                        <p class="font-medium" x-text="selected?.submitted_at ?? '—'"></p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-2">
                    <button type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100"
                        @click.stop="downloadPds()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <path d="M12 3v12" />
                            <path d="m8 11 4 4 4-4" />
                            <path d="M4 19h16" />
                        </svg>
                        Download PDS
                    </button>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl border border-slate-100 overflow-hidden flex flex-col min-h-0">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700">Preview</p>
                </div>
                <div class="flex-1 overflow-hidden">
                    <iframe
                        class="w-full h-full min-h-[500px] bg-white"
                        x-show="selected?.user_id"
                        :src="selected?.user_id ? `/pds-preview/${selected.user_id}?ts=${Date.now()}` : ''"
                        frameborder="0"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
