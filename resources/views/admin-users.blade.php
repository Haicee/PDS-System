<x-app-layout>
    <div class="py-10"
        x-data="{
            search: '',
            filterStatus: '',
            sortKey: 'name',
            sortDir: 'asc',
            admins: @js($admins),
            norm(v) { return (v ?? '').toString().trim().toLowerCase(); },
            init() {
                window.addEventListener('admin-updated', (e) => {
                    const updated = e.detail?.admin;
                    if (!updated?.email) return;
                    const target = this.norm(updated.email);
                    this.admins = this.admins.map(a => this.norm(a.email) === target ? updated : a);
                });
            },
            filteredSorted() {
                return this.admins
                    .filter(a => {
                        const q = this.search.toLowerCase();
                        const matchesSearch = !q || this.norm(a.name).includes(q) || this.norm(a.email).includes(q) || this.norm(a.role).includes(q);

                        const statusFilter = this.norm(this.filterStatus);
                        const matchesStatus = !statusFilter || this.norm(a.status) === statusFilter;
                        return matchesSearch && matchesStatus;
                    })
                    .sort((a, b) => {
                        const dir = this.sortDir === 'asc' ? 1 : -1;
                        const key = this.sortKey;

                        const av = this.norm(a[key]);
                        const bv = this.norm(b[key]);
                        const primary = av.localeCompare(bv);
                        if (primary !== 0) return primary * dir;

                        return this.norm(a.name).localeCompare(this.norm(b.name)) * dir;
                    });
            },
            clearFilters() {
                this.search = '';
                this.filterStatus = '';
                this.sortKey = 'name';
                this.sortDir = 'asc';
            }
        }"
        x-init="init()">

        <div class="mx-auto sm:px-6 lg:px-20 space-y-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide text-indigo-500 font-semibold">Admin Users</p>
                    <h1 class="text-2xl font-bold text-slate-900">Manage Admin Accounts</h1>
                    <p class="text-slate-500 text-sm">Review admin roles, status, and contact details.</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100">
                <div class="px-8 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-end border-b border-slate-100">
                    <div class="flex flex-col gap-3 w-full lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap gap-2">
                            <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="filterStatus">
                                <option value="">Status: All</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>

                            <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="sortKey">
                                <option value="name">Sort: Name</option>
                                <option value="email">Sort: Email</option>
                                <option value="role">Sort: Role</option>
                                <option value="status">Sort: Status</option>
                            </select>

                            <button type="button" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:border-indigo-300 hover:text-indigo-600"
                                x-on:click="sortDir = sortDir === 'asc' ? 'desc' : 'asc'" x-text="sortDir === 'asc' ? 'Asc' : 'Desc'"></button>

                            <button type="button" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:border-rose-200 hover:text-rose-600"
                                x-on:click="clearFilters()">Reset</button>
                        </div>

                        <div class="relative w-full lg:w-64">
                            <input type="text" placeholder="Search admin" class="w-full rounded-2xl border border-slate-200 py-2 ps-9 pe-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
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
                                <th class="px-6 py-3">Admin</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Created</th>
                                <th class="px-6 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                            <template x-for="(admin, idx) in filteredSorted()" :key="idx">
                                <tr class="hover:bg-slate-50"
                                    x-data="{
                                        key: 'admin-details-' + idx,
                                        admin,
                                        statusClass() { return this.norm(admin.status) === 'active' ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50'; },
                                        norm(v) { return (v ?? '').toString().trim().toLowerCase(); }
                                    }"
                                    x-init="window.addEventListener('admin-updated', e => { if (e.detail?.key === key) { admin = e.detail.admin; } })"
                                    x-cloak>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img :src="admin.avatar" :alt="admin.name + ' avatar'" class="h-10 w-10 rounded-full object-cover shadow-sm">
                                            <div>
                                                <p class="font-semibold text-slate-900" x-text="admin.name"></p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-slate-500" x-text="admin.email"></td>
                                    <td class="px-6 py-4" x-text="admin.role"></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="statusClass()"
                                            x-text="admin.status"></span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500" x-text="admin.created_at"></td>
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
        </div>
        @foreach ($admins as $index => $admin)
            <x-view-admin-modal :admin="$admin" :name="'admin-details-' . $index" :key="'admin-details-' . $index" width="2xl" />
        @endforeach
    </div>
</x-app-layout>
