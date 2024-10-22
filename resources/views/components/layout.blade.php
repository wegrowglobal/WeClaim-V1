@include('components.header')

    <div class="flex">
        <!-- Navigation Sidebar -->
        <div class="navbar-container lay-left">
           @include('components.navbar')
        </div>


        <!-- Content Here -->
        <div class="content-container lay-right">
            {{ $slot }}
        </div>
    </div>

@include('components.footer')
