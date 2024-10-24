<x-layout class="profile-page">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-wgg-white p-10 rounded-lg border border-wgg-border shadow-lg w-full max-w-2xl space-y-4">
            <div class="text-center">
                <h1 class="heading-1">Profile Settings</h1>
            </div>

            <form action="{{ route('profile.update') }}"  method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="space-y-6">
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
                    <div class="grid grid-cols-2 gap-4">
                        @if(!auth()->user()->first_name || !auth()->user()->second_name || !auth()->user()->email || !auth()->user()->phone || !auth()->user()->address || !auth()->user()->city)
                            <div class="col-span-2 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                                <strong class="font-semibold">Notice!</strong>
                                <span class="block sm:inline text-xs"> Please complete your profile information.</span>
                            </div>
                        @endif
                        <div class="relative">
                            <input id="first_name" name="first_name" class="form-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" type="text" value="{{ auth()->user()->first_name }}" placeholder=" " required>
                            <label for="first_name" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">First Name</label>
                        </div>
                        <div class="relative">
                            <input id="second_name" name="second_name" class="form-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" type="text" value="{{ auth()->user()->second_name }}" placeholder=" " required>
                            <label for="second_name" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">Second Name</label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="relative">
                            <input id="email" name="email" class="form-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" type="email" value="{{ auth()->user()->email }}" placeholder=" " required>
                            <label for="email" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">Email</label>
                        </div>
                        <div class="relative">
                            <input id="phone" name="phone" class="form-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" type="tel" value="{{ auth()->user()->phone }}" placeholder=" " required>
                            <label for="phone" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">Phone</label>
                        </div>
                    </div>

                    <div class="relative">
                        <textarea id="address" name="address" class="form-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" placeholder=" " required>{{ auth()->user()->address }}</textarea>
                        <label for="address" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">Address</label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="relative">
                            <input id="city" name="city" class="form-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" type="text" value="{{ auth()->user()->city }}" placeholder=" " required>
                            <label for="city" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">City</label>
                        </div>
                        <div class="relative">
                            <select id="state" name="state" class="form-input text-wgg-black-950 w-full px-4 py-2 pt-6 bg-wgg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" required>
                                <option value="">Select a state</option>
                                <option value="Johor" {{ auth()->user()->state == 'Johor' ? 'selected' : '' }}>Johor</option>
                                <option value="Kedah" {{ auth()->user()->state == 'Kedah' ? 'selected' : '' }}>Kedah</option>
                                <option value="Kelantan" {{ auth()->user()->state == 'Kelantan' ? 'selected' : '' }}>Kelantan</option>
                                <option value="Melaka" {{ auth()->user()->state == 'Melaka' ? 'selected' : '' }}>Melaka</option>
                                <option value="Negeri Sembilan" {{ auth()->user()->state == 'Negeri Sembilan' ? 'selected' : '' }}>Negeri Sembilan</option>
                                <option value="Pahang" {{ auth()->user()->state == 'Pahang' ? 'selected' : '' }}>Pahang</option>
                                <option value="Perak" {{ auth()->user()->state == 'Perak' ? 'selected' : '' }}>Perak</option>
                                <option value="Perlis" {{ auth()->user()->state == 'Perlis' ? 'selected' : '' }}>Perlis</option>
                                <option value="Pulau Pinang" {{ auth()->user()->state == 'Pulau Pinang' ? 'selected' : '' }}>Pulau Pinang</option>
                                <option value="Sabah" {{ auth()->user()->state == 'Sabah' ? 'selected' : '' }}>Sabah</option>
                                <option value="Sarawak" {{ auth()->user()->state == 'Sarawak' ? 'selected' : '' }}>Sarawak</option>
                                <option value="Selangor" {{ auth()->user()->state == 'Selangor' ? 'selected' : '' }}>Selangor</option>
                                <option value="Terengganu" {{ auth()->user()->state == 'Terengganu' ? 'selected' : '' }}>Terengganu</option>
                                <option value="Wilayah Persekutuan Kuala Lumpur" {{ auth()->user()->state == 'Wilayah Persekutuan Kuala Lumpur' ? 'selected' : '' }}>Wilayah Persekutuan Kuala Lumpur</option>
                                <option value="Wilayah Persekutuan Labuan" {{ auth()->user()->state == 'Wilayah Persekutuan Labuan' ? 'selected' : '' }}>Wilayah Persekutuan Labuan</option>
                                <option value="Wilayah Persekutuan Putrajaya" {{ auth()->user()->state == 'Wilayah Persekutuan Putrajaya' ? 'selected' : '' }}>Wilayah Persekutuan Putrajaya</option>
                            </select>
                            <label for="state" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">State</label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="relative">
                            <input id="zip_code" name="zip_code" class="form-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" type="text" value="{{ auth()->user()->zip_code }}" placeholder=" " required>
                            <label for="zip_code" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">Zip Code</label>
                        </div>
                        <div class="relative">
                            <input id="country" name="country" class="form-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" type="text" value="{{ auth()->user()->country }}" placeholder=" " required>
                            <label for="country" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">Country</label>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <a href="" class="btn flex-center bg-red-400 hover:bg-red-600">
                            Reset Password
                        </a>
                        <button type="submit" class="btn flex-center bg-green-400 hover:bg-green-600">
                            Save Profile
                        </button>

                        @if (session('success'))
                            <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 ease-in-out opacity-100 translate-y-0"
                                x-data="{ show: true }"
                                x-show="show"
                                x-init="setTimeout(() => show = false, 3000)"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform translate-y-2">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>{{ session('success') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.querySelector('.profile-picture');
                    const existingImg = container.querySelector('img');
                    const existingDiv = container.querySelector('div:not([class*="absolute"])');

                    if (existingDiv) {
                        existingDiv.remove();
                    }

                    if (existingImg) {
                        existingImg.src = e.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('w-full', 'h-full', 'object-cover');
                        container.insertBefore(img, container.firstChild);
                    }
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</x-layout>
