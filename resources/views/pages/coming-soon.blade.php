@extends('layouts.auth')

@section('content')
    <div
        class="flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-wgg-black-950 to-wgg-black-800 md:bg-gradient-to-br md:from-wgg-black-950 md:to-wgg-black-800">
        <div class="h-full w-full overflow-hidden bg-white md:h-auto md:max-w-md md:rounded-3xl md:shadow-2xl">
            <div class="flex min-h-screen flex-col justify-center px-8 py-12 md:min-h-0">
                <h1 class="mb-2 text-2xl font-bold text-gray-900">System Under Maintenance</h1>
                <p class="mb-6 text-sm text-gray-500">
                    We're upgrading WeClaims to serve you better. Please check back later.
                </p>

                <div class="space-y-6">
                    <div>
                        <h2 class="mb-2 text-lg font-semibold text-gray-900">Finished Features</h2>
                        <ul class="list-inside list-disc text-sm text-gray-600">
                            <li>Claims Dashboard with Status Tracking</li>
                            <li>Multi-level Approval Workflow</li>
                            <li>Role-based Access Control</li>
                            <li>Email Notifications System</li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="mb-2 text-lg font-semibold text-gray-900">Upcoming Features</h2>
                        <ul class="list-inside list-disc text-sm text-gray-600">
                            <li>Multiple Claim Types</li>
                            <li>Advanced Analytics Dashboard</li>
                            <li>Bulk Claims Processing</li>
                            <li>Enhanced Report Generation</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-wgg-black-800 px-4 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-wgg-black-950 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            type="submit">
                            Return to Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
