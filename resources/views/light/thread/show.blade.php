@extends(config('theme.layout'))

@section('scripts')
<!-- summernote js -->
<script src="{{ asset('third-party/summernote-0.8.12/summernote-bs4.min.js') }}" defer></script>
@endsection

@section('styles')
<!-- summernote css -->
<link href="{{ asset('third-party/summernote-0.8.12/summernote-bs4.css') }}" rel="stylesheet">
@endsection

@section('css')
.quotefrom {
    background: #eeeeee;
    color: crimson;
}

.quotefrom > strong {
    color: slategray;
}

.blockquote {
    background: #eeeeee;
    border-left: 5px solid lightsteelblue;
    padding-left: 5px;
}
@endsection

@section('content')
<div class="container">
    @if (!$thread->is_open)
    <section class="mb-3">
        <div class="alert alert-warning mb-0" role="alert">
            {{ __('This thread has been closed!') }}
        </div>
    </section>
    @endif
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
                        @canany(['update', 'delete', 'open', 'pin'], $thread)
                        <div class="col-2">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="threadManageButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="threadManageButton">
                                    @can('update', $thread)
                                    <a href="{{ route('thread.update', ['thread' => $thread->id, 'redirect=thread.show']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $thread)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#threadDeleteModal" 
                                        onclick="$('#threadDeleteModal #deleteButton').attr('href', '{{ route('thread.delete', ['thread' => $thread->id, 'redirect=topic.show']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                    @canany(['open', 'pin'], $thread)
                                    <div class="dropdown-divider"></div>
                                    @can('open', $thread)
                                    <a href="{{ route($thread->is_open ? 'thread.close' : 'thread.open', ['thread' => $thread->id, 'redirect=thread.show']) }}" class="dropdown-item">{{ $thread->is_open ? __('Close') : __('Open') }}</a>
                                    @endcan
                                    @can('pin', $thread)
                                    <a href="{{ route($thread->is_pinned ? 'thread.unpin' : 'thread.pin', ['thread' => $thread->id, 'redirect=thread.show']) }}" class="dropdown-item">{{ $thread->is_pinned ? __('Unpin') : __('Pin') }}</a>
                                    @endcan
                                    @endcanany
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
                            <a href="{{ route('user.show', ['user' => $comment->user->id]) }}">
                                <strong @if ($comment->user->is_admin)style="color: hotpink"@elseif ($comment->user->badge && $comment->user->badge == 'Moderator')style="color: seagreen"@endif>
                                    {{ $comment->user->name }}
                                </strong>
                            </a><br />
                            <img src="@isset($comment->user->photo){{ asset('storage') . '/' . $comment->user->photo }}@else{{ asset('img/64x64.png') }}@endisset" class="rounded-circle img-border" width="64" height="64"><br />
                            <h6 class="mt-2">
                                @if ($comment->user->is_admin)
                                <span class="badge badge-info">{{ __('Admin') }}</span>
                                @elseif ($comment->user->badge && $comment->user->badge != 'None')
                                <span class="badge badge-info">{{ __($comment->user->badge) }}</span>
                                @endif
                            </h6>
                            <small>{{ __('Member Since') }}<br />{{ $comment->user->created_at->format('Y-m-d') }}</small><br />
                            <small>{{ __('Posted At') }}<br />{{ $comment->created_at->format('Y-m-d H:i') }}</small>
                        </div>
                        <div class="col-md-10">
                            <div class="row h-100">
                                <div id="commentContent-{{ $comment->id }}" class="@auth @canany(['update', 'delete'], $comment){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                                    {!! $comment->content !!}
                                </div>
                                @auth
                                @can('comment', $thread)
                                @canany(['update', 'delete'], $comment)
                                <div class="col-2">
                                    <div class="dropdown float-right">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="commentManageButton-{{ $comment->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                        <div class="dropdown-menu" aria-labelledby="commentManageButton-{{ $comment->id }}">
                                            @can('update', $comment)
                                            <a href="{{ route('comment.update', ['comment' => $comment->id, 'redirect=thread.show']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                            @endcan
                                            @can('delete', $comment)
                                            <a href="#" class="dropdown-item" 
                                                data-toggle="modal" data-target="#commentDeleteModal" 
                                                onclick="$('#commentDeleteModal #deleteButton').attr('href', '{{ route('comment.delete', ['comment' => $comment->id, 'redirect=thread.show']) }}')">{{ __('Delete') }}</a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                                @endcanany
                                @endcan
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
                    @if ($comments->lastPage() > 1)
                    <div class="row mt-3">
                        <div class="col-12 d-flex justify-content-center">
                        {{ $comments->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            @auth
            @can('comment', $thread)
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
            @endcan
            @canany(['subscribe', 'favorite'], $thread)
            <div class="card mt-4">
                <div class="card-header">
                    {{ __('Options') }}
                </div>

                <div class="card-body">
                    <div class="row">
                        @can('subscribe', $thread)
                        <div class="col-md-2">
                            <a href="{{ route($is_subscribed ? 'thread.unsubscribe' : 'thread.subscribe', ['thread' => $thread->id, 'redirect=thread.show']) }}" class="btn {{ $is_subscribed ? 'btn-warning' : 'btn-primary' }}">
                                {{ __($is_subscribed ? 'Unsubscribe' : 'Subscribe') }}
                            </a>
                        </div>
                        <div class="col-md-4">
                            Subscribe to this thread and receive a notification in your email when a new comment is posted.
                        </div>
                        @endcan
                        @can('favorite', $thread)
                        <div class="col-md-2">
                            <a href="{{ route($is_favorited ? 'thread.unfavorite' : 'thread.favorite', ['thread' => $thread->id, 'redirect=thread.show']) }}" class="btn {{ $is_favorited ? 'btn-warning' : 'btn-primary' }}">
                                {{ __($is_favorited ? 'Unfavorite' : 'Favorite') }}
                            </a>
                        </div>
                        <div class="col-md-4">
                            Favorite this thread to save it in your favorite threads list.
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
            @endcanany
            @endauth
        </div>
    </div>
</div>
<!-- Thread Delete Modal -->
<div class="modal fade" id="threadDeleteModal" tabindex="-1" role="dialog" aria-labelledby="threadDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="threadDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{ __('Do you really want to delete this thread?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteButton" href="#" class="btn btn-danger">{{ __('Delete') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- Comment Delete Modal -->
<div class="modal fade" id="commentDeleteModal" tabindex="-1" role="dialog" aria-labelledby="commentDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{ __('Do you really want to delete this comment?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteButton" href="#" class="btn btn-danger">{{ __('Delete') }}</a>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        $('#newCommentContent').summernote({
            height: 150,
            placeholder: {!! '\'' . __('Write here...') . '\'' !!},
            codeviewFilter: true,
            maximumImageFileSize: 1024 * 1024, // 1 MB 
            callbacks: { 
                onImageUploadError: function (msg) { alert(msg + " {{ __('Choose images less than equal to 1 MB.') }}"); }
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
