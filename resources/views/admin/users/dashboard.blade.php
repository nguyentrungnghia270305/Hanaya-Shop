{{-- resources/views/admin/users/dashboard.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container mx-auto">
    <h2 class="text-xl font-bold mb-4">User Dashboard</h2>
    <div class="mb-4">
        <a href="{{ route('admin.user.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Add New User</a>
    </div>
    <div class="mb-4">
        <form method="GET" action="{{ route('admin.user.index') }}">
            <input type="text" name="search" placeholder="Search users..." class="border rounded p-2">
            <button type="submit" class="bg-gray-500 text-white px-3 py-1 rounded">Search</button>
        </form>
    </div>
    <div>
        @include('admin.users.partials.table_rows')
    </div>
</div>
@endsection
