<div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6 pb-4 shadow-lg">
    <div class="flex h-16 shrink-0 items-center">
        {{-- Logo --}}
        <a href="{{ route('home') }}">
             <svg class="h-8 w-auto text-gray-900" viewBox="0 0 557 438" fill="none">
                <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="currentColor"/>
                <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="currentColor"/>
                <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="currentColor"/>
            </svg>
        </a>
    </div>
    <nav class="flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul role="list" class="-mx-2 space-y-1">
                    {{-- Home Link --}}
                    <li>
                        <a href="{{ route('home') }}"
                           class="{{ request()->routeIs('home') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                            <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('home') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            Home
                        </a>
                    </li>

                    @auth
                        {{-- Staff Links --}}
                        @if (Auth::user()->role_id === 1)
                            <li>
                                <a href="{{ route('claims.dashboard') }}"
                                   class="{{ request()->routeIs('claims.dashboard') || request()->routeIs('claims.show') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                                    <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('claims.dashboard') || request()->routeIs('claims.show') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    My Claims
                                </a>
                            </li>
                             <li>
                                <a href="{{ route('claims.new') }}"
                                   class="{{ request()->routeIs('claims.new') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                                    <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('claims.new') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    New Claim
                                </a>
                            </li>
                        @endif

                        {{-- Approver Links --}}
                        @if (Auth::user()->role_id != 1)
                            <li>
                                <a href="{{ route('claims.approval') }}"
                                   class="{{ request()->routeIs('claims.approval') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                                     <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('claims.approval') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Approval Queue
                                </a>
                            </li>
                        @endif
                        
                        {{-- HR Links --}}
                        @if (Auth::user()->role_id === 3)
                             <li>
                                <a href="{{ route('claims.bulk-email') }}" 
                                   class="{{ request()->routeIs('claims.bulk-email') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                                    <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('claims.bulk-email') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                    Bulk Email
                                </a>
                            </li>
                        @endif

                        {{-- Admin Links --}}
                        @if (Auth::check() && Auth::user()->role_id === 5)
                            <li>
                                <a href="{{ route('admin.claims.index') }}"
                                   class="{{ request()->routeIs('admin.claims.index') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                                     <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.claims.index') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0h9.75" />
                                    </svg>
                                    Manage Claims
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.users.index') }}"
                                   class="{{ request()->routeIs('admin.users.index') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                                    <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.users.index') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    Manage Users
                                </a>
                            </li>
                             <li>
                                <a href="{{ route('admin.system.failed-logins') }}"
                                   class="{{ request()->routeIs('admin.system.failed-logins') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                                     <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.system.failed-logins') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                    Failed Logins
                                </a>
                            </li>
                            {{-- Consider adding System Config link if needed --}}
                         @endif
                    @endauth
                </ul>
            </li>
           
            {{-- Bottom Icon Navigation --}}
            <li class="mt-auto">
                 <div class="grid grid-cols-3 gap-4 py-2 border-t border-gray-200">
                    {{-- Profile Link --}}
                    <a href="{{ route('profile.show') }}" 
                       class="flex flex-col items-center justify-center rounded-md p-2 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-700 {{ request()->routeIs('profile.show') ? 'bg-gray-100 text-gray-700' : '' }}">
                        <svg class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Profile
                    </a>
                    
                    {{-- Settings Link (Placeholder) --}}
                    <a href="#" 
                       class="flex flex-col items-center justify-center rounded-md p-2 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <svg class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg>
                        Settings
                    </a>
                    
                    {{-- Logout Button --}}
                    <form method="POST" action="{{ route('logout') }}" class="contents">
                        @csrf
                        <button type="submit" 
                                class="flex flex-col items-center justify-center rounded-md p-2 text-xs font-semibold text-gray-500 hover:bg-red-50 hover:text-red-600">
                             <svg class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                            <span class="whitespace-nowrap">Sign out</span>
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>
</div> 