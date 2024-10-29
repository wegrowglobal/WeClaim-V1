<div class="flex flex-col items-center space-y-4">
    <div class="relative w-20 h-20 md:w-32 md:h-32 rounded-full overflow-hidden group profile-picture">
        @if(auth()->user()->profile_picture && Storage::disk('public')->exists(auth()->user()->profile_picture))
            <img src="{{ Storage::url('public/' . auth()->user()->profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center text-4xl font-bold text-white" style="background-color: {{ '#' . substr(md5(auth()->user()->id), 0, 6) }}">
                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
            </div>
        @endif
        <label for="profile_picture" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
            <span class="text-sm font-medium hidden md:inline">Update Photo</span>
            <span class="text-sm font-medium md:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                </svg>
            </span>
            <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="hidden">
        </label>
    </div>
</div>