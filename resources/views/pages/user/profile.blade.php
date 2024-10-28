@extends('layouts.app')

@section('content')
<div class="bg-white p-10 rounded-lg border border-wgg-border shadow-lg w-full flex justify-center items-center">
    <div class="flex w-full">
        <div class="w-full space-y-4">
            <div class="text-center">
                <h1 class="heading-1">Profile Settings</h1>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <x-profile.photo-upload />
                
                <x-profile.personal-info />
                
                <x-profile.contact-info />
                
                <x-profile.address-info :stateOptions="$stateOptions" />
                
                <x-profile.action-buttons />
                
                <x-profile.success-message />
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
@endpush