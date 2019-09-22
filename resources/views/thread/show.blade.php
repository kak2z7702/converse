@extends('layouts.app')

@section('scripts')
<!-- summernote js -->
<script src="{{ asset('third-party/summernote-0.8.12/summernote-bs4.min.js') }}" defer></script>
@endsection

@section('styles')
<!-- summernote css -->
<link href="{{ asset('third-party/summernote-0.8.12/summernote-bs4.css') }}" rel="stylesheet">

<style>
    .quotefrom {
        background: #eeeeee;
        color: crimson;
    }

    .quotefrom > strong {
        color: slategray;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="@auth @canany(['update', 'delete'], $thread){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                            <div @auth @canany(['update', 'delete'], $thread){!! 'class="mt-1"' !!}@endcan @endauth>
                                <a href="{{ route('index') }}">{{ __('Home') }}</a> > 
                                <a href="{{ route('category.show', ['category_slug' => $thread->topic->category->slug]) }}">{{ $thread->topic->category->title }}</a> > 
                                <a href="{{ route('topic.show', ['category_slug' => $thread->topic->category->slug, 'topic_slug' => $thread->topic->slug]) }}">{{ $thread->topic->title }}</a> > 
                                <a href="{{ route('thread.show', ['category_slug' => $thread->topic->category->slug, 'topic_slug' => $thread->topic->slug, 'thread_slug' => $thread->slug]) }}">{{ $thread->title }}</a>
                            </div>
                        </div>
                        @auth
                        @canany(['update', 'delete'], $thread)
                        <div class="col-2">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="threadManageButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Manage</button>
                                <div class="dropdown-menu" aria-labelledby="threadManageButton">
                                    @can('update', $thread)
                                    <a href="{{ route('thread.update', ['thread' => $thread->id, 'redirect=thread.show']) }}" class="dropdown-item">Edit</a>
                                    @endcan
                                    @can('delete', $thread)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#threadDeleteModal" 
                                        onclick="$('#threadDeleteModal #deleteButton').attr('href', '{{ route('thread.delete', ['thread' => $thread->id, 'redirect=topic.show']) }}')">Delete</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                        @endauth
                    </div>
                </div>

                <div class="card-body">
                    @foreach ($comments as $comment)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="col-md-2 text-center">
                            <strong @if ($comment->user->is_admin)style="color: hotpink"@endif>{{ $comment->user->name }}</strong><br />
                            <img src="@isset($comment->user->photo){{ asset('storage') . '/' . $comment->user->photo }}@else{{ asset('img/64x64.png') }}@endisset" class="rounded-circle img-border" width="64" height="64"><br />
                            @if ($comment->user->is_admin)<h6 class="mt-2"><span class="badge badge-info">{{ __('Admin') }}</span></h6>@endif
                            <small>{{ __('Member Since') }}<br />{{ $comment->user->created_at->format('Y-m-d') }}</small><br />
                            <small>{{ __('Posted At') }}<br />{{ $comment->created_at->format('Y-m-d H:i') }}</small>
                        </div>
                        <div class="col-md-10">
                            <div class="row h-100">
                                <div id="commentContent-{{ $comment->id }}" class="@auth @canany(['update', 'delete'], $comment){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                                    {!! $comment->content !!}
                                </div>
                                @auth
                                @canany(['update', 'delete'], $comment)
                                <div class="col-2">
                                    <div class="dropdown float-right">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="commentManageButton-{{ $comment->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Manage</button>
                                        <div class="dropdown-menu" aria-labelledby="commentManageButton-{{ $comment->id }}">
                                            @can('update', $comment)
                                            <a href="{{ route('comment.update', ['comment' => $comment->id, 'redirect=thread.show']) }}" class="dropdown-item">Edit</a>
                                            @endcan
                                            @can('delete', $comment)
                                            <a href="#" class="dropdown-item" 
                                                data-toggle="modal" data-target="#commentDeleteModal" 
                                                onclick="$('#commentDeleteModal #deleteButton').attr('href', '{{ route('comment.delete', ['comment' => $comment->id, 'redirect=thread.show']) }}')">Delete</a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                                @endcanany
                                @endauth
                                @if ($comment->user != auth()->user())
                                <div class="col-12 d-flex align-items-end">
                                    @can('create', 'App\Comment')
                                    <button class="btn btn-primary btn-sm mt-1" onclick="reply('{{ $comment->user->name }}', 'commentContent-{{ $comment->id }}')">{{ __('Reply To') }}</button>
                                    @endcan
                                    @can('create', 'App\Message')
                                    <a href="{{ route('message.create', ['receiver' => $comment->user->id]) }}" class="btn btn-primary btn-sm mt-1 ml-1">{{ __('Message To') }}</a>
                                    @endcan
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if (!$loop->last)<hr />@endif
                    @endforeach
                    @if ($comments->count() > $comments->perPage())
                    <div class="row mt-3">
                        <div class="col-12 d-flex justify-content-center">
                        {{ $comments->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @auth
            @can('create', 'App\Comment')
            <div class="card mt-4">
                <div class="card-header">
                    {{ __('Reply') }}
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('comment.create', ['thread' => $thread->id, 'redirect=thread.show']) }}">
                        @csrf

                        <div class="form-group row">
                            <div class="col-md-12">
                                <textarea id="newCommentContent" class="form-control @error('content') is-invalid @enderror" name="content">{{ old('content') }}</textarea>

                                @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                                <button type="button" class="btn btn-secondary" onclick="clearNewComment()">{{ __('Clear') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endcan
            @endauth
        </div>
    </div>
</div>
<!-- Thread Delete Modal -->
<div class="modal fade" id="threadDeleteModal" tabindex="-1" role="dialog" aria-labelledby="threadDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="threadDeleteModal">Are you sure?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                Do you really want to delete this thread?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a id="deleteButton" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>
<!-- Comment Delete Modal -->
<div class="modal fade" id="commentDeleteModal" tabindex="-1" role="dialog" aria-labelledby="commentDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentDeleteModal">Are you sure?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                Do you really want to delete this comment?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a id="deleteButton" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        $('#newCommentContent').summernote({
            height: 150,
            placeholder: 'Write here...',
            codeviewFilter: true,
            maximumImageFileSize: 1024 * 1024, // 1 MB 
            callbacks: { 
                onImageUploadError: function (msg) { alert(msg + " Choose images less than equal to 1 MB."); }
            }
        });

        $('#newCommentContent').summernote('code', '');
    });

    /**
     * Reply to a comment.
     * 
     * @param {String} comment Tag id of the comment being replied to.
     * @return void
     */
    function reply(from, comment)
    {
        let content = $('#' + comment).html();
        let html = '<div class="quotefrom">Quote <strong>@' + from + '</strong></div>' + '<blockquote class="blockquote">' + content + '</blockquote>';
        let code = $('#newCommentContent').summernote('code');

        if (code == '<br>') code = '';

        $('#newCommentContent').summernote('code', code + html);
        $('#newCommentContent').summernote('focus');
    }

    /**
     * Clear new comment content.
     */
    function clearNewComment()
    {
        $('#newCommentContent').summernote('code', '');
        $('#newCommentContent').summernote('focus');
    }
</script>
@endsection
