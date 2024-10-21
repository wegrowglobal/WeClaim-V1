<x-layout>
    <div class="max-w-full shadow-sm rounded-lg border border-wgg-border">
        @guest
            <div class="bg-white overflow-hidden">
                <div class="px-6 py-8">
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-4">Welcome to WeClaims</h2>
                    <div class="flex items-center mb-4">
                        <span class="text-gray-600 mr-2">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Not Logged In</span>
                    </div>
                    <p class="text-gray-600 mb-6">Please log in to access your claims dashboard and submit new claims.</p>
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Log In
                    </a>
                </div>
            </div>
        @endguest

        @auth
            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <div class="px-6 py-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Welcome, {{ auth()->user()->name }}</h2>
                    <div class="flex items-center mb-6">
                        <span class="text-gray-600 mr-2">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Logged In</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h3>
                            <ul class="space-y-3">
                                <li>
                                    <a href="{{ route('claims.new') }}" class="text-blue-600 hover:text-blue-800 font-medium">Submit New Claim</a>
                                </li>
                                <li>
                                    <a href="{{ route('claims.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">View Claims Dashboard</a>
                                </li>
                                @if(auth()->user()->role->name === 'approver')
                                    <li>
                                        <a href="{{ route('claims.approval') }}" class="text-blue-600 hover:text-blue-800 font-medium">Review Pending Claims</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Recent Activity</h3>
                            @php
                                $recentClaims = \App\Models\Claim::where('user_id', auth()->id())
                                    ->with('locations')
                                    ->orderBy('created_at', 'desc')
                                    ->take(3)
                                    ->get();
                            @endphp
                            @if($recentClaims->count() > 0)
                                <ul class="space-y-4">
                                    @foreach($recentClaims as $claim)
                                        <li class="bg-white p-4 rounded-md shadow  hover:bg-gray-50">
                                            <a href="{{ route('claims.claim', $claim->id) }}" class="block">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-sm font-semibold text-gray-900">Claim #{{ $claim->id }}</span>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $claim->status === 'approved' ? 'bg-green-100 text-green-800' : ($claim->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                        {{ ucfirst($claim->status) }}
                                                    </span>
                                                </div>
                                                <p class="mt-1 text-sm text-gray-600">By: {{ $claim->user->first_name . $claim->user->second_name }}</p>
                                                <p class="mt-1 text-sm text-gray-600">Total Distance: {{ $claim->total_distance }} KM</p>
                                                <p class="mt-1 text-sm text-gray-600">Locations: {{ $claim->locations->count() }}</p>
                                                <p class="mt-1 text-sm text-gray-600">Date: {{ $claim->date_from->format('d M Y') }} - {{ $claim->date_to->format('d M Y') }}</p>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-600">No recent claims found.</p>
                            @endif
                        </div>
                    </div>

                    <!-- New section: Claim Statistics -->
                    <div class="mt-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Claim Statistics</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @php
                                $totalClaims = \App\Models\Claim::where('user_id', auth()->id())->count();
                                $approvedClaims = \App\Models\Claim::where('user_id', auth()->id())->where('status', 'approved')->count();
                                $pendingClaims = \App\Models\Claim::where('user_id', auth()->id())->where('status', 'pending')->count();
                                $rejectedClaims = \App\Models\Claim::where('user_id', auth()->id())->where('status', 'rejected')->count();
                            @endphp
                            <div class="bg-blue-100 p-4 rounded-lg">
                                <p class="text-sm font-medium text-blue-800">Total Claims</p>
                                <p class="text-2xl font-bold text-blue-900">{{ $totalClaims }}</p>
                            </div>
                            <div class="bg-green-100 p-4 rounded-lg">
                                <p class="text-sm font-medium text-green-800">Approved Claims</p>
                                <p class="text-2xl font-bold text-green-900">{{ $approvedClaims }}</p>
                            </div>
                            <div class="bg-yellow-100 p-4 rounded-lg">
                                <p class="text-sm font-medium text-yellow-800">Pending Claims</p>
                                <p class="text-2xl font-bold text-yellow-900">{{ $pendingClaims }}</p>
                            </div>
                            <div class="bg-red-100 p-4 rounded-lg">
                                <p class="text-sm font-medium text-red-800">Rejected Claims</p>
                                <p class="text-2xl font-bold text-red-900">{{ $rejectedClaims }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- New section: Recent System Updates -->
                    <div class="mt-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Recent System Updates</h3>
                        <ul class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <li class="text-sm text-gray-700">• N/A</li>
                            <li class="text-sm text-gray-700">• N/A</li>
                            <li class="text-sm text-gray-700">• N/A</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endauth
    </div>
</x-layout>
