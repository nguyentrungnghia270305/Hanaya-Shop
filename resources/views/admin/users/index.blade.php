{{-- File: resources/views/admin/users/index.blade.php --}}

@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1 class="text-2xl font-bold mb-4">Danh sách người dùng</h1>
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Tên</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Ngày tạo</th>
                    <th class="px-4 py-2">Hành động</th>
                </tr>
            </thead>
            <tbody>
                {{-- @foreach ($users as $user) --}}
                <tr>
                    <td class="border px-4 py-2">1</td>
                    <td class="border px-4 py-2">Nguyễn Văn A</td>
                    <td class="border px-4 py-2">nguyenvana@example.com</td>
                    <td class="border px-4 py-2">2025-10-14</td>
                    <td class="border px-4 py-2">
                        <a href="#" class="text-blue-500">Xem</a>
                        <a href="#" class="text-red-500 ml-2">Xóa</a>
                    </td>
                </tr>
                {{-- @endforeach --}}
            </tbody>
        </table>
    </div>
@endsection
