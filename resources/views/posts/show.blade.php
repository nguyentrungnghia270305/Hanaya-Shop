@extends('layouts.app')

@section('content')
<div class="container py-4">
    <a href="{{ route('posts.index') }}" class="btn btn-link mb-3">&larr; Back to all posts</a>
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">{{ $post->title }}</h2>
            <p class="text-muted">By {{ $post->author }} | {{ optional($post->published_at)->format('d/m/Y') }}</p>
            <div class="mt-3 mb-3">{!! nl2br(e($post->content)) !!}</div>
        </div>
    </div>
</div>
@endsection
