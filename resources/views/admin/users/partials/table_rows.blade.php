{{-- resources/views/admin/users/partials/table_rows.blade.php --}}
<table class="table-auto w-full">
    <thead>
        <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Email</th>
            <th class="px-4 py-2">Role</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td class="border px-4 py-2">{{ $user->id }}</td>
            <td class="border px-4 py-2">{{ $user->name }}</td>
            <td class="border px-4 py-2">{{ $user->email }}</td>
            <td class="border px-4 py-2">{{ $user->role }}</td>
            <td class="border px-4 py-2">
                <a href="{{ route('admin.user.show', $user->id) }}" class="text-blue-500">View</a>
                <a href="{{ route('admin.user.edit', $user->id) }}" class="text-green-500 ml-2">Edit</a>
                <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 ml-2" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
