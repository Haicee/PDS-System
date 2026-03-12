<x-app-layout>
    <div class="py-10">
        <div class="mx-auto sm:px-6 lg:px-20 space-y-10">

            <!-- Personal Data Sheet quick access card -->
            <section class="w-full">
                <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-1">
                        <p class="text-sm uppercase tracking-wide text-emerald-600 font-semibold">Personal Data Sheet</p>
                        <h2 class="text-xl font-bold text-slate-900">View or edit your PDS</h2>
                        <p class="text-sm text-slate-600">Open your PDS to review details or continue editing any section.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        @php
                            $pdsInfo = $stats['pds'] ?? [];
                            $hasSubmission = $pdsInfo['has_submission'] ?? false;
                            $latestStatus = $pdsInfo['latest_status'] ?? null;
                            $editAllowed = $pdsInfo['edit_allowed'] ?? true;
                            $editRequestStatus = $pdsInfo['edit_request_status'] ?? null;
                            $isPendingRequest = $editRequestStatus === 'pending';
                            $isRejectedRequest = $editRequestStatus === 'rejected';
                        @endphp

                        @if($editAllowed)
                            <a href="{{ route('pds.form1') }}" class="inline-flex justify-center items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                                Edit / Resume PDS
                            </a>
                        @else
                            <form method="POST" action="{{ route('profile.requestEdit') }}" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                                @csrf
                                <button type="submit" {{ $isPendingRequest ? 'disabled' : '' }}
                                    class="inline-flex justify-center items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border border-slate-200 {{ $isPendingRequest ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-white text-emerald-700 hover:bg-emerald-50 hover:border-emerald-200' }}">
                                    {{ $isPendingRequest ? 'Edit request pending' : 'Request PDS edit' }}
                                </button>
                            </form>

                            <div class="text-xs text-slate-500">
                                @if($isPendingRequest)
                                    Waiting for admin approval to edit your submitted PDS.
                                @elseif($isRejectedRequest)
                                    Your previous edit request was rejected. You may send a new request.
                                @else
                                    Your PDS is approved. Request admin approval to edit.
                                @endif
                            </div>
                        @endif
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
