@include('components.header')

    <div id="notification-container" class="fixed top-4 right-4 z-50">
        <!-- Notifications will be inserted here -->
    </div>


    <div class="flex">
        <!-- Navigation Sidebar -->
        <div class="navbar-container lay-left">
           @include('components.navbar')
        </div>


        <!-- Content Here -->
        <div class="content-container {{ Route::currentRouteName() === 'profile' ? 'profile-content' : '' }}">
            {{ $slot }}
        </div>
    </div>


@include('components.footer')
