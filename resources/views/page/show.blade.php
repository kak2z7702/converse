@extends('layouts.app')

@section('content')
<div class="container">
    @auth
    @canany(['update', 'delete'], $page)
    <div class="dropdown">
        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="pageManageButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
        <div class="dropdown-menu" aria-labelledby="pageManageButton">
            @can('update', $page)
            <a href="{{ route('page.update', ['page' => $page->id, 'redirect=page.show']) }}" class="dropdown-item">{{ __('Edit') }}</a>
            @endcan
            @can('delete', $page)
            <a href="#" class="dropdown-item" 
                data-toggle="modal" data-target="#pageDeleteModal" 
                onclick="$('#pageDeleteModal #deleteButton').attr('href', '{{ route('page.delete', ['page' => $page->id]) }}')">{{ __('Delete') }}</a>
            @endcan
        </div>
    </div>
    @endcanany
    @endauth
    <h1 class="mt-3">{{ $page->title }}</h1>
    <p>{!! $page->content !!}</p>
    @can('comment', $page)
    <a name="comments"></a>
    <h2>{{ __('Comments') }}</h2>
    <hr />
    @forelse ($comments as $comment)
    <div class="row mt-2">
        <div class="@auth @canany(['update', 'delete'], $comment){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
            <img src="@isset($comment->user->photo){{ asset('storage') . '/' . $comment->user->photo }}@else{{ asset('img/64x64.png') }}@endisset" class="rounded-circle img-border float-left mr-3 d-none d-lg-block d-xl-block" width="64" height="64">
            <h5 class="mt-2 mb-1">
                <a href="{{ route('user.show', ['user' => $comment->user->id]) }}">{{ $comment->user->name }}</a> @ {{ $comment->created_at->format('Y-m-d H:i') }}
            </h5>
            <p>{{ $comment->content }}</p>
        </div>
        @auth
        @canany(['update', 'delete'], $comment)
        <div class="col-2 pt-1">
            <div class="dropdown float-right">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="commentManageButton-{{ $comment->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                <div class="dropdown-menu" aria-labelledby="commentManageButton-{{ $comment->id }}">
                    @can('update', $comment)
                    <a href="{{ route('comment.update', ['comment' => $comment->id, 'redirect=page.show']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                    @endcan
                    @can('delete', $comment)
                    <a href="#" class="dropdown-item" 
                        data-toggle="modal" data-target="#commentDeleteModal" 
                        onclick="$('#commentDeleteModal #deleteButton').attr('href', '{{ route('comment.delete', ['comment' => $comment->id, 'redirect=page.show']) }}')">{{ __('Delete') }}</a>
                    @endcan
                </div>
            </div>
        </div>
        @endcanany
        @endauth
    </div>
    @empty
    <p>{{ __('No comments have been posted to this page.') }}</p>
    @endforelse
    @if ($comments->count() >= $comments->perPage())
    <div class="row mt-3">
        <div class="col-12 d-flex justify-content-center">
        {{ $comments->fragment('comments')->links() }}
        </div>
    </div>
    @endif
    @auth
    @can('create', 'App\Comment')
    <div class="card mt-4">
        <div class="card-header">
            {{ __('Reply') }}
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('comment.create', ['page' => $page->id, 'redirect=page.show']) }}">
                @csrf

                <div class="form-group row">
                    <div class="col-md-12">
                        <textarea id="newCommentContent" class="form-control @error('content') is-invalid @enderror" name="content" required>{{ old('content') }}</textarea>

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
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endcan
    @endauth
    @endcan
</div>
<!-- Page Delete Modal -->
<div class="modal fade" id="pageDeleteModal" tabindex="-1" role="dialog" aria-labelledby="pageDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{ __('Do you really want to delete this page?') }}
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
@endsection
