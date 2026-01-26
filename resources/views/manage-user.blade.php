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
                    <a href="{{ route('manage-user.export') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500">
                        Export Excel
                    </a>
                </div>
            </div>

            <!-- Search functionality, add if needed-->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100" 
                x-data="{ 
                    search: '',
                    filterStatus: '',
                    filterType: '',
                    sortKey: 'name',
                    sortDir: 'asc',
                    employees: @js($employees),
                    filteredSorted() {
                        const norm = (v) => (v ?? '').toString().trim().toLowerCase();

                        return this.employees
                            .filter(e => {
                                const q = this.search.toLowerCase();
                                const matchesSearch = !q || norm(e.name).includes(q) || norm(e.phone).includes(q) || norm(e.email).includes(q);

                                const statusFilter = norm(this.filterStatus);
                                const matchesStatus = !statusFilter || norm(e.status) === statusFilter;

                                const typeFilter = norm(this.filterType);
                                const matchesType = !typeFilter || norm(e.type) === typeFilter;
                                return matchesSearch && matchesStatus && matchesType;
                            })
                            .sort((a, b) => {
                                const dir = this.sortDir === 'asc' ? 1 : -1;
                                const key = this.sortKey;

                                if (key === 'status') {
                                    const order = { active: 1, inactive: 2 };
                                    const av = order[norm(a.status)] ?? 99;
                                    const bv = order[norm(b.status)] ?? 99;
                                    if (av !== bv) return (av - bv) * dir;
                                }

                                const av = norm(a[key]);
                                const bv = norm(b[key]);
                                const primary = av.localeCompare(bv);
                                if (primary !== 0) return primary * dir;

                                // Secondary tiebreak by name for stability
                                return norm(a.name).localeCompare(norm(b.name)) * dir;
                            });
                    },
                    clearFilters() {
                        this.search = '';
                        this.filterStatus = '';
                        this.filterType = '';
                        this.sortKey = 'name';
                        this.sortDir = 'asc';
                    }
                }">
                <div class="px-8 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-end border-b border-slate-100">
                    <div class="flex flex-col gap-3 w-full lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap gap-2">
                            <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="filterStatus">
                                <option value="">Status: All</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                            <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="filterType">
                                <option value="">Type: All</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Job On Call">Job On Call</option>
                            </select>
                            <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="sortKey">
                                <option value="name">Sort: Name</option>
                                <option value="department">Sort: Department</option>
                                <option value="status">Sort: Status</option>
                            </select>
                            <button type="button" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:border-indigo-300 hover:text-indigo-600"
                                x-on:click="sortDir = sortDir === 'asc' ? 'desc' : 'asc'" x-text="sortDir === 'asc' ? 'Asc' : 'Desc'"></button>
                            <button type="button" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:border-rose-200 hover:text-rose-600"
                                x-on:click="clearFilters()">Reset</button>
                        </div>
                        <div class="relative w-full lg:w-64">
                            <input type="text" placeholder="Search employee" class="w-full rounded-2xl border border-slate-200 py-2 ps-9 pe-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                x-model.debounce.200ms="search" x-on:keydown.escape="search = ''" />
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
                            <template x-for="(employee, idx) in filteredSorted()" :key="idx">
                                <tr class="hover:bg-slate-50"
                                    x-data="{
                                        key: 'employee-details-' + idx,
                                        employee,
                                        statusClass() { return this.employee.status === 'Active' ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50'; },
                                    }"
                                    x-init="window.addEventListener('employee-updated', e => { if (e.detail?.key === key) { employee = e.detail.employee; } })"
                                    x-cloak>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img :src="employee.avatar" :alt="employee.name + ' avatar'" class="h-10 w-10 rounded-full object-cover shadow-sm">
                                            <div>
                                                <p class="font-semibold text-slate-900" x-text="employee.name"></p>
                                                <span class="text-slate-500" x-text="employee.type"></span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4" x-text="employee.department"></td>
                                    <td class="px-6 py-4 text-slate-500" x-text="employee.email"></td>
                                    <td class="px-6 py-4" x-text="employee.phone"></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass()" x-text="employee.status"></span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500" x-text="employee.location"></td>
                                    <td class="px-6 py-4 text-center">
                                        <div>
                                            <button type="button" class="inline-flex items-center rounded-full border border-indigo-200 px-4 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50"
                                                x-on:click.prevent="window.dispatchEvent(new CustomEvent('open-modal', { detail: key }));">
                                                View
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            @foreach ($employees as $index => $employee)
                <x-view-user-modal :employee="$employee" :name="'employee-details-' . $index" :key="'employee-details-' . $index" width="2xl" />
            @endforeach
        </div>

    </div>
</x-app-layout>