<x-app-layout>

    <div class="py-10"
        x-data="{
            addEmployeeOpen: false,
            addAdminOpen: false,
            confirmOpen: false,
            confirmType: '',
            confirmTitle: '',
            confirmBody: '',

            newEmployeeName: '',
            savingEmployee: false,
            employeeError: '',
            employeeFieldErrors: {},
            adminName: '',
            adminEmail: '',
            adminPassword: '',

            adminPasswordConfirm: '',
            savingAdmin: false,
            adminError: '',
            adminFieldErrors: {},

            init() {
                // reserved for future outer-level setup
            },
            openEmployee() {
                this.employeeError = '';
                this.employeeFieldErrors = {};
                this.newEmployeeName = '';
                this.savingEmployee = false;
                this.addEmployeeOpen = true;
            },

            openAdmin() {
                this.adminError = '';
                this.adminFieldErrors = {};
                this.savingAdmin = false;

                this.adminName = '';
                this.adminEmail = '';
                this.adminPassword = '';

                this.adminPasswordConfirm = '';
                this.addAdminOpen = true;
            },
            closeEmployee() {
                this.addEmployeeOpen = false;
            },
            closeAdmin() {
                this.addAdminOpen = false;
            },
            closeConfirm() {
                this.confirmOpen = false;
                this.confirmType = '';
                this.confirmTitle = '';
                this.confirmBody = '';
            },
            requestConfirm(type) {
                if (type === 'admin') {
                    if (this.savingAdmin) return;
                    this.confirmTitle = 'Create Admin Account';
                    this.confirmBody = 'This will give {adminName} full access to manage settings and users. Are you sure?';
                } else {
                    if (this.savingEmployee) return;
                    this.confirmTitle = 'Add New Employee';
                    this.confirmBody = 'You are about to add {newEmployeeName} to the directory. Are you sure?';
                }       
                this.confirmType = type;
                this.confirmOpen = true;
            },
            confirmSubmit() {
                if (this.confirmType === 'admin') {
                    this.closeConfirm();
                    this.submitAdmin();
                } else if (this.confirmType === 'employee') {
                    this.closeConfirm();
                    this.submitEmployee();
                }
            },
            submitEmployee() {
                if (this.savingEmployee) return;
                this.employeeError = '';
                this.employeeFieldErrors = {};
                this.savingEmployee = true;

                fetch('{{ route('registration-users.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        full_name: this.newEmployeeName,
                    })
                })
                .then(async (res) => {
                    if (res.redirected || res.status === 302) {
                        throw new Error('Session expired or unauthorized. Please log in as an admin.');
                    }

                    const data = await res.json().catch(() => null);
                    const errors = data?.errors || {};

                    if (res.status !== 201) {
                        this.employeeFieldErrors = errors;

                        let msg = data?.message;
                        if (!msg && res.status === 422) {
                            const combined = Object.values(errors).flat().join(' ') || '';
                            if (combined.toLowerCase().includes('already been taken')) {
                                msg = 'Name already exists.';
                            } else {
                                msg = 'Please fix the highlighted errors.';
                            }
                        }
                        if (!msg && (res.status === 401 || res.status === 403)) {
                            msg = 'You are not authorized to add employees.';
                        }
                        if (!msg) {
                            msg = res.status >= 500 ? 'Server error. Please try again.' : 'Unable to add employee. Please try again.';
                        }

                        throw new Error(msg);
                    }

                    this.addEmployeeOpen = false;
                    this.newEmployeeName = '';
                    this.employeeFieldErrors = {};
                })
                .catch(err => {
                    this.employeeError = err.message || 'Unable to add employee.';
                })
                .finally(() => {
                    this.savingEmployee = false;
                });
            },

            submitAdmin() {
                if (this.savingAdmin) return;
                this.adminError = '';
                this.adminFieldErrors = {};
                this.savingAdmin = true;

                // Client-side guards
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(this.adminEmail.trim())) {
                    this.savingAdmin = false;
                    this.adminFieldErrors = { email: ['Please enter a valid email address.'] };
                    this.adminError = 'Please fix the errors and try again.';
                    return;
                }

                if (this.adminPassword.length < 8) {
                    this.savingAdmin = false;
                    this.adminFieldErrors = { password: ['Password must be at least 8 characters.'] };
                    this.adminError = 'Please fix the errors and try again.';
                    return;
                }

                if (this.adminPassword !== this.adminPasswordConfirm) {
                    this.savingAdmin = false;
                    this.adminFieldErrors = { password: ['Passwords do not match.'] };
                    this.adminError = 'Please fix the errors and try again.';
                    return;
                }

                fetch('{{ route('admin-users.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''

                    },
                    body: JSON.stringify({
                        name: this.adminName,
                        email: this.adminEmail,
                        password: this.adminPassword,
                        password_confirmation: this.adminPasswordConfirm,
                    })
                })
                .then(async (res) => {
                    if (res.redirected || res.status === 302) {
                        throw new Error('Session expired or unauthorized. Please log in as an admin.');
                    }

                    const data = await res.json().catch(() => null);
                    const errors = data?.errors || {};

                    if (res.status !== 201) {
                        this.adminFieldErrors = errors;

                        let msg = data?.message;
                        if (!msg && res.status === 422) {
                            const combined = Object.values(errors).flat().join(' ') || '';
                            if (combined.toLowerCase().includes('already been taken')) {
                                msg = 'Name or email is already in use.';
                            } else {
                                msg = 'Please fix the highlighted errors.';
                            }
                        }
                        if (!msg && (res.status === 401 || res.status === 403)) {
                            msg = 'You are not authorized to create admins.';
                        }
                        if (!msg) {
                            msg = res.status >= 500 ? 'Server error. Please try again.' : 'Unable to create admin. Please try again.';
                        }

                        throw new Error(msg);
                    }

                    this.addAdminOpen = false;
                    this.adminName = '';
                    this.adminEmail = '';
                    this.adminPassword = '';
                    this.adminPasswordConfirm = '';
                    this.adminFieldErrors = {};
                })
                .catch(err => {
                    this.adminError = err.message || 'Unable to create admin.';
                })

                .finally(() => {
                    this.savingAdmin = false;
                });
            }
        }"
        x-init="init()"
        x-cloak>
        

        <div class="mx-auto sm:px-6 lg:px-20 space-y-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide text-indigo-500 font-semibold">Team Directory</p>
                    <h1 class="text-2xl font-bold text-slate-900">Manage Employees</h1>
                    <p class="text-slate-500 text-sm">Review account status, employee type, and contact details in one place.</p>
                </div>
                <div class="flex gap-3">
                    <button type="button"
                        @click="openAdmin()"
                        class="inline-flex items-center rounded-xl border border-indigo-200 px-4 py-2 text-sm font-medium text-indigo-600 bg-white hover:bg-indigo-50 shadow-sm">
                        Add Admin
                    </button>

                    <button type="button"
                        @click="openEmployee()"
                        class="inline-flex items-center rounded-xl border border-indigo-200 px-4 py-2 text-sm font-medium text-indigo-600 bg-white hover:bg-indigo-50 shadow-sm">
                        Add Employee
                    </button>

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
                    init() {
                        window.addEventListener('employee-deleted', (e) => {
                            const id = e.detail?.id;
                            const email = e.detail?.email;
                            if (id !== undefined && id !== null) {
                                this.removeEmployeeById(id);
                            } else if (email) {
                                this.removeEmployeeByEmail?.(email);
                            }
                        });
                    },
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
                    },
                    removeEmployeeById(id) {
                        const targetId = Number(id);
                        this.employees = this.employees.filter(e => Number(e.id) !== targetId);
                    },
                    removeEmployeeByEmail(email) {
                        const norm = (v) => (v ?? '').toString().trim().toLowerCase();
                        this.employees = this.employees.filter(e => norm(e.email) !== norm(email));
                    }
                }"
                x-init="init()">
                <div class="px-8 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-end border-b border-slate-100">
                    <div class="flex flex-col gap-3 w-full lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap gap-2">
                            <select class="w-28 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="filterStatus">
                                <option value="">Status: All</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                            <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="filterType">
                                <option value="">Type: All</option>
                                <option value="Permanent Employee">Permanent</option>
                                <option value="Job On Site">Job On Call</option>
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
                            <input type="text" placeholder="Search employee" class="w-full rounded-2xl border border-slate-200 py-2 ps-9 pe-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
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
                                <th class="px-6 py-3">Unit</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Phone</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Place Of Assignment</th>
                                <th class="px-6 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                            <template x-for="employee in filteredSorted()" :key="employee.id">
                                <tr class="hover:bg-slate-50"
                                    x-data="{
                                        key: 'employee-details-' + employee.id,
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

                                    <td class="px-6 py-4" x-text="employee.unit"></td>
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

            @foreach ($employees as $employee)
                <x-view-user-modal :employee="$employee" :name="'employee-details-' . $employee['id']" :key="'employee-details-' . $employee['id']" width="2xl" />
            @endforeach

            <!-- Add Employee Modal -->
            <div x-show="addEmployeeOpen" class="fixed inset-0 flex items-center justify-center bg-slate-900/50 px-4"
                x-transition.opacity @click.self="closeEmployee()">
                <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-slate-100"
                    x-transition.scale>
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                        <div>
                            <h2 class="text-lg uppercase text-slate-700 font-semibold">Add Employee</h2>
                            <p class="text-xs text-slate-500">Add a new employee.</p>
                        </div>
                        <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100"
                            @click="closeEmployee()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-5 py-4 space-y-4">
                        <div class="space-y-2">
                            <label for="new-person-name" class="text-sm font-medium text-slate-700">Full name</label>
                            <input id="new-person-name" type="text" x-model="newEmployeeName" placeholder="e.g. Juan Dela Cruz"
                                class="uppercase w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                            <template x-if="employeeFieldErrors?.full_name">
                                <p class="text-sm text-rose-600" >The name has already been taken.</p>
                            </template>
                            
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white"
                            @click="closeEmployee()">Cancel</button>
                        <button type="button"
                            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="!newEmployeeName.trim() || savingEmployee"
                            @click="requestConfirm('employee')">
                            <span x-show="!savingEmployee">Save</span>
                            <span x-show="savingEmployee">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add Admin Modal -->
            <div x-show="addAdminOpen" class="fixed inset-0 flex items-center justify-center bg-slate-900/50 px-4"
                x-transition.opacity @click.self="closeAdmin()">
                <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-slate-100"
                    x-transition.scale>
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                        <div>
                            <h2 class="text-lg uppercase text-slate-700 font-semibold">Add admin</h2>
                            <p class="text-xs text-slate-500">Create an admin account with credentials.</p>
                        </div>
                        <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100"
                            @click="closeAdmin()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-5 py-4 space-y-4">
                        <div class="space-y-2">
                            <label for="admin-name" class="text-sm font-medium text-slate-700">Full name</label>
                            <input id="admin-name" type="text" x-model="adminName" placeholder="e.g. Admin User"
                                class="uppercase w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                            <template x-if="adminFieldErrors?.name">
                                <p class="text-sm text-rose-600" x-text="adminFieldErrors.name[0]"></p>
                            </template>
                        </div>
                        <div class="space-y-2">
                            <label for="admin-email" class="text-sm font-medium text-slate-700">Email</label>
                            <input id="admin-email" type="email" x-model="adminEmail" placeholder="admin@example.com"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                            <template x-if="adminFieldErrors?.email">
                                <p class="text-sm text-rose-600" x-text="adminFieldErrors.email[0]"></p>
                            </template>
                            <template x-if="adminFieldErrors?.email === undefined && adminEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(adminEmail)">
                                <p class="text-sm text-rose-600">Please enter a valid email address.</p>
                            </template>
                        </div>
                        <div class="space-y-2">
                            <label for="admin-password" class="text-sm font-medium text-slate-700">Password</label>
                            <input id="admin-password" type="password" x-model="adminPassword" placeholder="••••••••"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                            <template x-if="adminFieldErrors?.password">
                                <p class="text-sm text-rose-600" x-text="adminFieldErrors.password[0]"></p>
                            </template>
                            <template x-if="adminFieldErrors?.password === undefined && adminPassword && adminPassword.length < 8">
                                <p class="text-sm text-rose-600">Password must be at least 8 characters.</p>
                            </template>
                        </div>

                        <div class="space-y-2">
                            <label for="admin-password-confirm" class="text-sm font-medium text-slate-700">Confirm Password</label>
                            <input id="admin-password-confirm" type="password" x-model="adminPasswordConfirm" placeholder="••••••••"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                            <template x-if="adminPassword && adminPasswordConfirm && adminPassword !== adminPasswordConfirm">
                                <p class="text-sm text-rose-600">Passwords do not match.</p>
                            </template>
                        </div>
                       
                    </div>

                    <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white"
                            @click="closeAdmin()">Cancel</button>
                        <button type="button"
                            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="savingAdmin || !(adminName.trim() && adminEmail.trim() && adminPassword && adminPasswordConfirm)"
                            @click="requestConfirm('admin')">
                            <span x-show="!savingAdmin">Save</span>
                            <span x-show="savingAdmin">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Confirm Modal -->
            <div x-show="confirmOpen" class="fixed inset-0 flex items-center justify-center bg-slate-900/60 px-4"
                x-transition.opacity @click.self="closeConfirm()">
                <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl border border-slate-100" x-transition.scale>
                    <div class="px-5 py-4 border-b border-slate-200 flex items-start justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900" x-text="confirmTitle || 'Please confirm'"></h3>
                            <p class="text-sm text-slate-500" x-text="confirmBody || 'Are you sure?'"></p>
                        </div>
                        <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100"
                            @click="closeConfirm()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="px-5 py-4 flex items-center justify-end gap-3">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white"
                            @click="closeConfirm()">Not now</button>
                        <button type="button" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="(confirmType === 'admin' && savingAdmin) || (confirmType === 'employee' && savingEmployee)"
                            @click="confirmSubmit()">Confirm</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>