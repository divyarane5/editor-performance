@extends('layouts.app')

@section('content')
<div class="container py-10">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Upload Performance Report</h2>

    @if(session('success'))
        <div class="alert alert-success mb-6">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
@endsection