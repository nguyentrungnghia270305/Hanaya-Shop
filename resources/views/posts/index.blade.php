@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">All Posts</h1>
    <div class="row">
        @foreach($posts as $post)
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $post->title }}</h5>
                        <p class="card-text">{{ Str::limit($post->content, 120) }}</p>
                        <p class="card-text"><small class="text-muted">By {{ $post->author }} | {{ optional($post->published_at)->format('d/m/Y') }}</small></p>
                        <a href="{{ route('posts.show', $post->id) }}" class="btn btn-primary">Read more</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">
        {{ $posts->links() }}
    </div>
</div>
@endsection
