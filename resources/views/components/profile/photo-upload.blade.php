<div class="flex flex-col items-center space-y-4">
    <div class="relative w-32 h-32 rounded-full overflow-hidden group profile-picture">
        @if(auth()->user()->profile_picture && Storage::disk('public')->exists(auth()->user()->profile_picture))
            <img src="{{ Storage::url('public/' . auth()->user()->profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center text-4xl font-bold text-white" style="background-color: {{ '#' . substr(md5(auth()->user()->id), 0, 6) }}">
                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
            </div>
        @endif
        <label for="profile_picture" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
            <span class="text-sm font-medium">Update Photo</span>
            <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="hidden">
        </label>
    </div>
</div>