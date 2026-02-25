<x-app-layout>
    <div class="max-w-3xl mx-auto py-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Upload Performance Report</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

       <form action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" required>
            <button type="submit">Upload</button>
        </form>
    </div>
</x-app-layout>