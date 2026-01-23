<x-app-layout>
    

    <div class="py-10">
        <div class="mx-auto sm:px-6 lg:px-20 space-y-8">

            <!--Cards on dashboard  max-w-7x-->
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-2">
                <div class="bg-white rounded-2xl shadow flex items-center gap-5 p-6 border border-slate-100">
                    <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <!-- User icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Permanent Employees</p>
                        <p class="text-3xl font-semibold text-slate-900">{{ number_format($stats['totalEmployees']) }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow flex items-center gap-5 p-6 border border-slate-100">
                    <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <!-- Shield check icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15l4.5-4.5m3.75-2.25-7.5-3-7.5 3v6.75c0 4.148 2.94 7.879 7.5 8.74 4.56-.861 7.5-4.592 7.5-8.74V8.25Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Job On Call</p>
                        <p class="text-3xl font-semibold text-slate-900">{{ number_format($stats['verifiedEmployees']) }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <div class="bg-white rounded-2xl shadow flex items-center gap-5 p-6 border border-slate-100">
                    <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <!-- User icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Pending PDS</p>
                        <p class="text-3xl font-semibold text-slate-900">{{ number_format($stats['totalEmployees']) }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow flex items-center gap-5 p-6 border border-slate-100">
                    <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <!-- Shield check icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15l4.5-4.5m3.75-2.25-7.5-3-7.5 3v6.75c0 4.148 2.94 7.879 7.5 8.74 4.56-.861 7.5-4.592 7.5-8.74V8.25Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Approved PDS</p>
                        <p class="text-3xl font-semibold text-slate-900">{{ number_format($stats['verifiedEmployees']) }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow flex items-center gap-5 p-6 border border-slate-100">
                    <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <!-- Shield check icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15l4.5-4.5m3.75-2.25-7.5-3-7.5 3v6.75c0 4.148 2.94 7.879 7.5 8.74 4.56-.861 7.5-4.592 7.5-8.74V8.25Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Rejected PDS</p>
                        <p class="text-3xl font-semibold text-slate-900">{{ number_format($stats['verifiedEmployees']) }}</p>
                    </div>
                </div>
            </div>

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
                                <th class="px-6 py-3">Department</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Phone</th>
                                <th class="px-6 py-3">Location Assigned</th>
                                <th class="px-6 py-3">Submitted</th>
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
                                    <td class="px-6 py-4">{{ $submission['department'] }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $submission['email'] }}</td>
                                    <td class="px-6 py-4">{{ $submission['phone'] }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $submission['location'] }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $submission['submitted_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
