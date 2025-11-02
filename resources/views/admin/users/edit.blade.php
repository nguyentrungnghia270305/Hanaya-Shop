{{-- resources/views/admin/users/edit.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container mx-auto">
    <h2 class="text-xl font-bold mb-4">Edit User</h2>
    <form action="{{ route('admin.user.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="name" class="block">Name</label>
            <input type="text" name="name" id="name" class="border rounded w-full p-2" value="{{ $user->name }}" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block">Email</label>
            <input type="email" name="email" id="email" class="border rounded w-full p-2" value="{{ $user->email }}" required>
        </div>
        <div class="mb-4">
            <label for="password" class="block">Password (leave blank to keep current)</label>
            <input type="password" name="password" id="password" class="border rounded w-full p-2">
        </div>
        <div class="mb-4">
            <label for="role" class="block">Role</label>
            <select name="role" id="role" class="border rounded w-full p-2">
                <option value="user" @if($user->role == 'user') selected @endif>User</option>
                <option value="admin" @if($user->role == 'admin') selected @endif>Admin</option>
            </select>
        </div>
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>
@endsection
