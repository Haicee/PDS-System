@props([
    'admin',
    'name',
    'key' => null,
    'width' => '2xl',
])

<x-modal :name="$name" :max-width="$width">
    <div class="p-10 space-y-6"
        x-data="{
            key: @js($key ?? $name),
            admin: @js($admin),
            working: {},
            init() { this.reset(); },
            reset() { this.working = JSON.parse(JSON.stringify(this.admin)); },
            save() {
                this.admin = JSON.parse(JSON.stringify(this.working));
                window.dispatchEvent(new CustomEvent('admin-updated', { detail: { key: this.key, admin: this.admin } }));
                this.$dispatch('close');
            },
            statusClass() { return this.working.status === 'Active' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; },
            roleClass() { return this.working.role === 'Main Admin' ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-700'; },
        }">
            <div class="flex items-center gap-4">
                <div class="relative h-20 w-20">
                    <img :src="working.avatar" :alt="working.name + ' avatar'"
                        class="h-20 w-20 rounded-full object-cover shadow">
                </div>
                <div class="space-y-2 flex-1">
                    <div class="flex-col gap-1">
                        <input type="text" class="px-2 py-1 text-2xl font-semibold text-slate-900 border-slate-200 rounded-xl p-0 focus:ring-0 focus:outline-none"
                            x-model="working.name" />
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" :class="roleClass()" x-text="working.role"></span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <span x-text="working.status"></span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 text-sm text-slate-700">
                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500">Email</span>
                    <input type="email" class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" x-model="working.email" />
                </label>

                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500">Role</span>
                    <select class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" x-model="working.role">
                        <option>Main Admin</option>
                        <option>Admin User</option>
                    </select>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500">Status</span>
                    <select class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" x-model="working.status">
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500">Created</span>
                    <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 text-slate-500" x-model="working.created_at" readonly />
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                <x-primary-button x-on:click="save()">Save changes</x-primary-button>
            </div>
    </div>
</x-modal>
