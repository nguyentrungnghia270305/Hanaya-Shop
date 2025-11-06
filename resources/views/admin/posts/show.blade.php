@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Post Detail') }}
    </h2>
@endsection

@section('content')
<div class="py-8">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3>{{ $post->title }}</h3>
            <p class="text-muted">By {{ $post->author }} | {{ optional($post->published_at)->format('d/m/Y') }}</p>
            <div class="mt-3 mb-3">{!! nl2br(e($post->content)) !!}</div>
            <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-link">Back to list</a>
        </div>
    </div>
</div>
@endsection
