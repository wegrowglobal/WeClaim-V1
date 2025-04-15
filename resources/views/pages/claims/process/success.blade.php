@section('content')
<div class="container mx-auto mt-8">
    <div class="bg-white p-6 rounded-lg shadow-md">
        @if(session('success'))
            <div class="text-green-600 font-bold">
                {{ session('success') }}
            </div>
        @endif
    </div>
</div>
@endsection