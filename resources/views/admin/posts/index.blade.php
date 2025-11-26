@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Posts Management') }}
    </h2>
@endsection

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <form method="GET" action="{{ route('admin.posts.index') }}" class="mb-4 flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or author..." class="form-control w-64" />
                <button type="submit" class="btn btn-secondary">Search</button>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-link">Clear</a>
            </form>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary mb-3">Add New Post</a>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <form method="POST" action="{{ route('admin.posts.bulk-delete') }}" id="bulk-delete-form">
                @csrf
                @method('DELETE')
                <table class="table table-bordered w-full">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Published At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($posts as $post)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $post->id }}"></td>
                            <td>{{ $post->id }}</td>
                            <td>{{ $post->title }}</td>
                            <td>{{ $post->author }}</td>
                            <td>{{ optional($post->published_at)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.posts.show', $post->id) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <button type="submit" class="btn btn-danger mt-2" onclick="return confirm('Delete selected posts?')">Delete Selected</button>
            </form>
            <script>
                document.getElementById('select-all').onclick = function() {
                    let checkboxes = document.querySelectorAll('input[name="ids[]"]');
                    for (let cb of checkboxes) cb.checked = this.checked;
                };
            </script>
            <div class="mt-4">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
