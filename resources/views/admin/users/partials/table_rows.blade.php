{{-- resources/views/admin/users/partials/table_rows.blade.php --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-4 py-2">ID</th>
                <th scope="col" class="px-4 py-2">Name</th>
                <th scope="col" class="px-4 py-2">Email</th>
                <th scope="col" class="px-4 py-2">Role</th>
                <th scope="col" class="px-4 py-2">Status</th>
                <th scope="col" class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($users as $user)
            <tr>
                <td class="px-4 py-2">{{ $user->id }}</td>
                <td class="px-4 py-2">{{ $user->name }}</td>
                <td class="px-4 py-2">{{ $user->email }}</td>
                <td class="px-4 py-2">{{ $user->role }}</td>
                <td class="px-4 py-2">
                    @if($user->active ?? true)
                        <span class="text-green-600 font-semibold">Active</span>
                    @else
                        <span class="text-red-600 font-semibold">Inactive</span>
                    @endif
                </td>
                <td class="px-4 py-2 flex gap-2">
                    <x-button-link :href="route('admin.user.show', $user->id)" color="blue">View</x-button-link>
                    <x-button-link :href="route('admin.user.edit', $user->id)" color="green">Edit</x-button-link>
                    <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" color="red" onclick="return confirm('Are you sure?')">Delete</x-button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
