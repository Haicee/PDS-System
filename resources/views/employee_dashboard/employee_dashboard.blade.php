<x-app-layout>
    <div class="py-10">
        <div class="mx-auto sm:px-6 lg:px-20 space-y-10">

            <!-- Personal Data Sheet quick access card -->
            <section class="w-full">
                @php
                    $pdsSubmitted = $stats['pds']['has_submission'] ?? false;
                    $pdsStatus = $stats['pds']['latest_status'] ?? null;
                    $canEditPds = !$pdsSubmitted || $pdsStatus === 'rejected';
                @endphp

                <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-1">
                        <p class="text-sm uppercase tracking-wide text-emerald-600 font-semibold">Personal Data Sheet</p>
                        <h2 class="text-xl font-bold text-slate-900">
                            {{ $canEditPds ? 'View or edit your PDS' : 'View your PDS' }}
                        </h2>
                        @if($canEditPds)
                            <p class="text-sm text-slate-600">Open your PDS to review details or continue editing any section.</p>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <a href="{{ $canEditPds ? route('pds.form1') : route('pds.view') }}" class="inline-flex justify-center items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                            {{ $canEditPds ? 'Edit/Resume PDS' : 'View PDS' }}
                        </a>
                    </div>
                </div>
            </section>

            <!-- Greeting + actions -->
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-2xl shadow-lg p-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <p class="text-sm uppercase tracking-wide text-white/80">Employee workspace</p>
                    <h1 class="text-2xl lg:text-3xl font-semibold">Welcome {{ auth()->user()->name ?? 'Employee' }}</h1>
                    <p class="text-white/80 text-sm lg:text-base">Manage your Personal Data Sheet, track review status, and upload supporting documents.</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
