@props([
    'employee',
    'name',
    'width' => 'md',
])

@php
    $typeStyles = $employee['type'] === 'Permanent'
        ? 'bg-emerald-50 text-emerald-600'
        : 'bg-indigo-50 text-indigo-600';

    $statusStyles = $employee['status'] === 'Active'
        ? 'bg-emerald-50 text-emerald-600'
        : 'bg-rose-50 text-rose-600';
@endphp

<x-modal :name="$name" :max-width="$width">
    <div class=" p-6 space-y-5">
        <div class="flex items-center gap-4">
            <img src="{{ $employee['avatar'] }}" alt="{{ $employee['name'] }} avatar" class="h-20 w-20 rounded-full object-cover shadow">
            <div class="space-y-1">
                <p class="text-2xl font-semibold text-slate-900">{{ $employee['name'] }}</p>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $typeStyles }}">
                    {{ $employee['type'] }}
                </span>
            </div>
        </div>

        <dl class="grid gap-4 text-sm text-slate-600">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <dt class="font-semibold text-slate-500">Department</dt>
                <dd>{{ $employee['department'] }}</dd>
            </div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <dt class="font-semibold text-slate-500">Email</dt>
                <dd class="text-slate-500">{{ $employee['email'] }}</dd>
            </div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <dt class="font-semibold text-slate-500">Phone</dt>
                <dd>{{ $employee['phone'] }}</dd>
            </div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <dt class="font-semibold text-slate-500">Status</dt>
                <dd>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles }}">
                        {{ $employee['status'] }}
                    </span>
                </dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="font-semibold text-slate-500">Location Assigned</dt>
                <dd class="text-right text-slate-500">{{ $employee['location'] }}</dd>
            </div>
        </dl>

        <div class="flex justify-end">
            <x-primary-button x-on:click="$dispatch('close')">Close</x-primary-button>
        </div>
    </div>
</x-modal>
