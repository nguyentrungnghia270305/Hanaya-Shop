@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Post') }}
    </h2>
@endsection

@section('content')
<div class="py-8">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <form action="{{ route('admin.posts.update', $post->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $post->title) }}" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea name="content" id="content" class="form-control" rows="5" required>{{ old('content', $post->content) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="author" class="form-label">Author</label>
                    <input type="text" name="author" id="author" class="form-control" value="{{ old('author', $post->author) }}" required>
                </div>
                <div class="mb-3">
                    <label for="published_at" class="form-label">Published At</label>
                    <input type="date" name="published_at" id="published_at" class="form-control" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d')) }}">
                </div>
                <button type="submit" class="btn btn-primary">Update Post</button>
            </form>
        </div>
    </div>
</div>
@endsection
