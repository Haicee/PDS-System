@props([
    'employee',
    'name',
    'key' => null,
    'width' => '2xl',
])

<x-modal :name="$name" :max-width="$width">
    <div class="p-10 space-y-6"
        x-data="{
            key: @js($key ?? $name),
            employee: @js($employee),
            working: {},
            init() { this.reset(); },
            reset() { this.working = JSON.parse(JSON.stringify(this.employee)); },
            saving: false,
            saveError: '',
            save() {
                if (this.saving) return;
                this.saving = true;
                this.saveError = '';

                const payload = {
                    name: this.working.name,
                    unit: this.working.unit,
                    email: this.working.email,
                    phone: this.working.phone,
                    type: this.working.type,
                    status: this.working.status,
                    location_assigned: this.working.location,
                };

                fetch('/manage-user/' + this.working.id, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(payload)
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => null);
                    if (!res.ok) {
                        const errors = data?.errors || {};
                        const msg = Object.values(errors).flat().join(' ') || data?.message || 'Failed to update user.';
                        throw new Error(msg);
                    }
                    this.employee = JSON.parse(JSON.stringify(this.working));
                    window.dispatchEvent(new CustomEvent('employee-updated', { detail: { key: this.key, employee: this.employee } }));
                    this.$dispatch('close');
                })
                .catch(err => {
                    this.saveError = err.message || 'Failed to update user.';
                })
                .finally(() => {
                    this.saving = false;
                });
            },
            statusClass() { return this.working.status === 'Active' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; },
            typeClass() { return this.working.type === 'Permanent Employee' ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600'; },
        }">
            <div class="flex items-center gap-4">
                <div class="relative h-20 w-20">
                    <img :src="working.avatar"
                        :alt="working.name + ' avatar'" 
                        class="h-20 w-20 rounded-full object-cover shadow">
                    <label class="absolute inset-0 flex items-center justify-center rounded-full bg-black/40 opacity-0 text-xs font-semibold text-white transition hover:opacity-100 cursor-pointer">
                        Change
                        <input type="file" class="sr-only" accept="image/*" x-on:change="(e) => { if (e.target.files[0]) { const url = URL.createObjectURL(e.target.files[0]); working.avatar = url; } }">
                    </label>
                </div>
                <div class="space-y-2 flex-1">
                <div class="flex-col gap-1">
                    <input type="text" class="px-2 py-1 text-2xl font-semibold text-slate-900 border-slate-200 rounded-xl p-0 focus:ring-0 focus:outline-none uppercase"
                        x-model="working.name"
                        x-on:input="working.name = (working.name || '').toUpperCase()" />
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" :class="typeClass()" x-text="working.type"></span>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <span x-text="working.unit"></span>
                </div>
            </div>
        </div>

        <div class="grid gap-4 text-sm text-slate-700">
            <label class="flex flex-col gap-1">
                <span class="font-semibold text-slate-500">Unit</span>
                <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 uppercase" 
                x-model="working.unit"
                x-on:input="working.unit = (working.unit || '').toUpperCase()" />
            </label>

            <label class="flex flex-col gap-1">
                <span class="font-semibold text-slate-500">Email</span>
                <input type="email" class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" x-model="working.email" />
            </label>

            <label class="flex flex-col gap-1">
                <span class="font-semibold text-slate-500">Phone</span>
                <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" x-model="working.phone" />
            </label>

            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">
                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500">Type</span>
                    <select class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" x-model="working.type">
                        <option value="Permanent Employee">Permanent Employee</option>
                        <option value="Job Order">Job Order</option>
                    </select>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500">Status</span>
                    <select class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" x-model="working.status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </label>
            </div>

            <label class="flex flex-col gap-1">
                <span class="font-semibold text-slate-500">Location Assigned</span>
                <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 uppercase" x-model="working.location"
                x-on:input="working.location = (working.location || '').toUpperCase()" />
            </label>
        </div>

        <template x-if="saveError">
            <p class="text-sm text-rose-600" x-text="saveError"></p>
        </template>

        <div class="flex items-center justify-between gap-3 pt-2">
            <div class="flex items-center gap-6">
                <button type="button" class="text-sm font-medium text-slate-500 hover:text-slate-700" x-on:click="reset()">Undo</button>
            </div>
            <div class="flex gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                <x-primary-button x-on:click="save()" x-bind:disabled="saving">
                    <span x-show="!saving">Save changes</span>
                    <span x-show="saving">Saving...</span>
                </x-primary-button>
            </div>
        </div>
    </div>
</x-modal>
