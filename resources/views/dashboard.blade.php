<x-app-layout>
    

    <div class="py-10">
        <div class="mx-auto sm:px-6 lg:px-20 space-y-10">

            <section class="flex flex-col items-center gap-10 py-6">
                <div class="grid gap-6 sm:grid-cols-5 justify-items-center">
                    <div class="rounded-2xl bg-gradient-to-r from-sky-500 to-blue-500 text-white shadow-md shadow-sky-200/40 border border-white/10 w-72 h-36 flex">
                        <div class="p-5 sm:p-6 flex flex-col justify-between w-full">
                            <p class="text-lg font-semibold">Permanent Employees</p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-round-icon lucide-users-round"><path d="M18 21a8 8 0 0 0-16 0"/><circle cx="10" cy="8" r="5"/><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/></svg>
                                    </span>
                                    <p class="text-4xl font-semibold leading-none">{{ number_format($stats['totalEmployees'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-gradient-to-r from-emerald-400 to-teal-500 text-white shadow-md shadow-emerald-200/40 border border-white/10 w-72 h-36 flex">
                        <div class="p-5 sm:p-6 flex flex-col justify-between w-full">
                            <p class="text-lg font-semibold">Job Order</p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-search-icon lucide-user-round-search"><circle cx="10" cy="8" r="5"/><path d="M2 21a8 8 0 0 1 10.434-7.62"/><circle cx="18" cy="18" r="3"/><path d="m22 22-1.9-1.9"/></svg>
                                    </span>
                                    <p class="text-4xl font-semibold leading-none">{{ number_format($stats['verifiedEmployees'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-gradient-to-r from-amber-400 to-orange-400 text-white shadow-md shadow-amber-200/40 border border-white/10 w-72 h-36 flex">
                        <div class="p-5 sm:p-6 flex-col justify-between w-full">
                            <p class="text-lg font-semibold">Pending PDS</p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock4-icon lucide-clock-4"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/></svg>
                                    </span>
                                    <p class="text-4xl font-semibold leading-none">{{ number_format($stats['pendingPds'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-gradient-to-r from-sky-400 to-cyan-400 text-white shadow-md shadow-sky-200/40 border border-white/10 w-72 h-36 flex">
                        <div class="p-5 sm:p-6 flex-col justify-between w-full">
                            <p class="text-lg font-semibold">Approved PDS</p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-todo-icon lucide-list-todo"><path d="M13 5h8"/><path d="M13 12h8"/><path d="M13 19h8"/><path d="m3 17 2 2 4-4"/><rect x="3" y="4" width="6" height="6" rx="1"/></svg>
                                    </span>
                                    <p class="text-4xl font-semibold leading-none">{{ number_format($stats['approvedPds'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-gradient-to-r from-rose-400 to-pink-500 text-white shadow-md shadow-rose-200/40 border border-white/10 w-72 h-36 flex">
                        <div class="p-5 sm:p-6 flex-col justify-between w-full">
                            <p class="text-lg font-semibold">Rejected PDS</p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x-icon lucide-circle-x"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                    </span>
                                    <p class="text-4xl font-semibold leading-none">{{ number_format($stats['rejectedPds'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
            </section>

            <div x-data="dashboardPreview(@js($stats['recentSubmissions']))" x-init="init()" class="space-y-6">

            <!-- Recent submissions table helps admins monitor latest activity -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100">
                <div class="px-8 py-4 flex items-center justify-between border-b border-slate-100">
                    <div>
                        <p class="text-base font-semibold text-slate-900">Latest PDS submissions</p>
                        <p class="text-sm text-slate-500">Track recent submissions</p>
                    </div>
                    <span class="text-sm text-slate-500 font-semibold">Updated {{ now()->format('M d, Y') }}</span>
                </div>


                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Employee</th>
                                <th class="px-6 py-3">Unit</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Phone</th>
                                <th class="px-6 py-3">Place of Assignment</th>
                                <th class="px-6 py-3">Date Submitted</th>
                                <th class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                            @foreach ($stats['recentSubmissions'] as $submission)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $submission['avatar'] }}" alt="{{ $submission['name'] }} avatar" class="h-10 w-10 rounded-full object-cover shadow-sm">
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $submission['name'] }}</p>
                                                <span class="text-slate-500">{{ $submission['type'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">{{ $submission['unit'] }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $submission['email'] }}</td>
                                    <td class="px-6 py-4">{{ $submission['phone'] }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $submission['location'] }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $submission['submitted_at'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full border border-indigo-200 px-4 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50"
                                            @click.prevent="openById({{ $submission['id'] }}, @js($submission))"
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- History of admin activity -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100">
                <div class="px-8 py-4 flex items-center justify-between border-b border-slate-100">
                    <div>
                        <p class="text-base font-semibold text-slate-900">"History of admin activity (Sample table for recent history of admin activity, need jud e connect database sori!)"</p>
                        <p class="text-sm text-slate-500">Track recent activity</p>
                    </div>
                    <span class="text-sm text-slate-500 font-semibold">Updated {{ now()->format('M d, Y') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Employee</th>
                                <th class="px-6 py-3">Unit</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Phone</th>
                                <th class="px-6 py-3">Place of Assignment</th>
                                <th class="px-6 py-3">Date Submitted</th>
                                <th class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                            @foreach ($stats['recentSubmissions'] as $submission)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $submission['avatar'] }}" alt="{{ $submission['name'] }} avatar" class="h-10 w-10 rounded-full object-cover shadow-sm">
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $submission['name'] }}</p>
                                                <span class="text-slate-500">{{ $submission['type'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">{{ $submission['unit'] }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $submission['email'] }}</td>
                                    <td class="px-6 py-4">{{ $submission['phone'] }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $submission['location'] }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $submission['submitted_at'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full border border-indigo-200 px-4 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50"
                                            @click.prevent="openById({{ $submission['id'] }}, @js($submission))"
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <x-pds-preview x-show="modalOpen" @close="close()" />
            </div>

        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    function dashboardPreview(submissions = []) {
        return {
            submissions,
            modalOpen: false,
            selected: null,
            open(submission) {
                this.selected = submission;
                this.modalOpen = true;
            },
            close() {
                this.modalOpen = false;
                this.selected = null;
            },
            requestConfirm() {
                window.location.href = '/pds-form';
            },
            downloadPds() {
                if (this.selected?.user_id) {
                    window.open(`/pds-preview/${this.selected.user_id}?ts=${Date.now()}`, '_blank');
                }
            },
        };
    }
</script>
@endpush
