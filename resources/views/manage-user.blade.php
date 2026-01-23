<x-app-layout>

    <div class="py-10">
        <div class="mx-auto sm:px-6 lg:px-20 space-y-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide text-indigo-500 font-semibold">Team Directory</p>
                    <h1 class="text-2xl font-bold text-slate-900">Manage Employees</h1>
                    <p class="text-slate-500 text-sm">Review account status, employee type, and contact details in one place.</p>
                </div>
                <div class="flex gap-3">
                    <button class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500">Export CSV</button>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100">
                <div class="px-8 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-end border-b border-slate-100">
                    
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                        <div class="relative">
                            <input type="text" placeholder="Search employee" class="w-full sm:w-64 rounded-2xl border border-slate-200 py-2 ps-9 pe-3 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <span class="absolute left-3 top-2.5 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0-6.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                            </span>
                        </div>
                        
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Employee</th>
                                <th class="px-6 py-3">Department</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Phone</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Location Assigned</th>
                                <th class="px-6 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                            @foreach ($employees as $employee)
                                @php
                                    $typeStyles = $employee['type'] === 'Permanent'
                                        ? 'bg-emerald-50 text-emerald-600'
                                        : 'bg-indigo-50 text-indigo-600';

                                    $statusStyles = match ($employee['status']) {
                                        'Active' => 'text-emerald-600 bg-emerald-50',
                                        default => 'text-rose-600 bg-rose-50',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $employee['avatar'] }}" alt="{{ $employee['name'] }} avatar" class="h-10 w-10 rounded-full object-cover shadow-sm">
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $employee['name'] }}</p>
                                                <span class="text-slate-500">
                                                    {{ $employee['type'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">{{ $employee['department'] }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $employee['email'] }}</td>
                                    <td class="px-6 py-4">{{ $employee['phone'] }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles }}">
                                            {{ $employee['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">{{ $employee['location'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <div x-data="{}">
                                            <button type="button" class="inline-flex items-center rounded-full border border-indigo-200 px-4 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50"
                                                x-on:click.prevent="console.log('Opening modal for', '{{ $employee['name'] }}'); window.dispatchEvent(new CustomEvent('open-modal', { detail: 'employee-details-{{ $loop->index }}' }));">
                                                View
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @foreach ($employees as $index => $employee)
                <x-view-user-modal :employee="$employee" :name="'employee-details-' . $index" width="md" />
            @endforeach
        </div>

    </div>
</x-app-layout>