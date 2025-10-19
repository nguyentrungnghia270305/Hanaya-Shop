{{-- resources/views/admin/users/partials/table_rows.blade.php --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-4 py-2"><input type="checkbox" id="select-all-users" title="Select all users" onclick="document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = this.checked)"></th>
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
            <tr tabindex="0" class="focus:bg-blue-50 @if($loop->even) bg-gray-100 @endif" title="User ID: {{ $user->id }}">
                <td class="px-4 py-2"><input type="checkbox" class="user-checkbox" value="{{ $user->id }}" title="Select user"></td>
                <td class="px-4 py-2" title="User ID">{{ $user->id }}</td>
                <td class="px-4 py-2" title="Full name">{{ $user->name }}</td>
                <td class="px-4 py-2" title="Email address">{{ $user->email }}</td>
                <td class="px-4 py-2" title="Role">{{ $user->role }}</td>
                <td class="px-4 py-2" title="Status">
                    @if($user->active ?? true)
                        <span class="text-green-600 font-semibold" title="Active">Active</span>
                    @else
                        <span class="text-red-600 font-semibold" title="Inactive">Inactive</span>
                    @endif
                </td>
                <td class="px-4 py-2 flex gap-2">
                    <x-button-link :href="route('admin.user.show', $user->id)" color="blue" title="View user details">View</x-button-link>
                    <x-button-link :href="route('admin.user.edit', $user->id)" color="green" title="Edit user">Edit</x-button-link>
                    <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" color="red" title="Delete user" onclick="return confirm('Are you sure?')">Delete</x-button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="px-4 py-2 bg-gray-50 text-right">
                    <span class="font-semibold">Total users: {{ $users->count() }}</span>
                    <button class="bg-red-500 text-white px-3 py-1 rounded ml-4" onclick="bulkDeleteUsers()">Delete Selected</button>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<div id="bulk-delete-status" class="mt-2 text-sm text-red-600" style="display:none"></div>
<div id="bulk-delete-loading" class="mt-2 text-sm text-blue-600" style="display:none">Deleting users...</div>
<script>
function bulkDeleteUsers() {
    const ids = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    const status = document.getElementById('bulk-delete-status');
    const loading = document.getElementById('bulk-delete-loading');
    status.style.display = 'none';
    loading.style.display = 'none';
    if(ids.length === 0) { status.textContent = 'No users selected!'; status.style.display = 'block'; return; }
    if(!confirm('Delete selected users?')) return;
    loading.style.display = 'block';
    // TODO: Implement AJAX bulk delete
    setTimeout(() => {
        loading.style.display = 'none';
        status.textContent = 'Bulk delete: ' + ids.join(', ');
        status.style.display = 'block';
    }, 1200);
}
</script>
