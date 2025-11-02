{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container mx-auto">
    <h2 class="text-xl font-bold mb-4">Create User</h2>
    <form action="{{ route('admin.user.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="name" class="block">Name</label>
            <input type="text" name="name" id="name" class="border rounded w-full p-2" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block">Email</label>
            <input type="email" name="email" id="email" class="border rounded w-full p-2" required>
        </div>
        <div class="mb-4">
            <label for="password" class="block">Password</label>
            <input type="password" name="password" id="password" class="border rounded w-full p-2" required>
        </div>
        <div class="mb-4">
            <label for="role" class="block">Role</label>
            <select name="role" id="role" class="border rounded w-full p-2">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Create</button>
    </form>
</div>
@endsection
