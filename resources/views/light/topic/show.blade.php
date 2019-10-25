@extends(config('theme.layout'))

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="@auth @canany(['update', 'delete'], $topic){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                            <div @auth @canany(['update', 'delete'], $topic){!! 'class="mt-1"' !!}@endcan @endauth>
                                <a href="{{ route('index') }}">{{ __('Home') }}</a> > 
                                <a href="{{ route('category.show', ['category_slug' => $topic->category->slug]) }}">{{ $topic->category->title }}</a> > 
                                <a href="{{ route('topic.show', ['category_slug' => $topic->category->slug, 'topic_slug' => $topic->slug]) }}">{{ $topic->title }}</a>
                            </div>
                        </div>
                        @auth
                        @canany(['update', 'delete'], $topic)
                        <div class="col-2">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="topicManageButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="topicManageButton">
                                    @can('update', $topic)
                                    <a href="{{ route('topic.update', ['topic' => $topic->id, 'redirect=topic.show']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $topic)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#topicDeleteModal" 
                                        onclick="$('#topicDeleteModal #deleteButton').attr('href', '{{ route('topic.delete', ['topic' => $topic->id, 'redirect=category.show']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                        @endauth
                    </div>
                </div>

                <div class="card-body">
                    @auth
                    @can('create', 'App\Thread')
                    <div class="row mb-3">
                        <div class="col-12"><a href="{{ route('thread.create', ['topic' => $topic->id, 'redirect=topic.show']) }}" class="btn btn-primary">{{ __('+ New Thread') }}</a></div>
                    </div>
                    @endcan
                    @endauth
                    @forelse ($threads as $thread)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="col-8">
                            <h5 class="mt-2 mb-1">
                                <a href="{{ route('thread.show', ['category_slug' => $topic->category->slug, 'topic_slug' => $topic->slug, 'thread_slug' => $thread->slug]) }}">{{ $thread->title }}</a>
                                @if (!$thread->is_open)
                                <span class="badge badge-danger">{{ __('Closed') }}</span>
                                @endif
                                @if ($thread->is_pinned)
                                <span class="badge badge-info">{{ __('Pinned') }}</span>
                                @endif
                            </h5>
                        </div>
                        <div class="@auth @canany(['update', 'delete'], $thread){{ 'col-2' }}@else{{ 'col-4' }}@endcanany @else{{ 'col-4' }}@endauth">
                            <div class="mt-2">
                                {{ __('By') }} <a href="{{ route('user.show', ['user' => $thread->user->id]) }}">{{ $thread->user->name }}</a><br />@ {{ $thread->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>
                        @auth
                        @canany(['update', 'delete', 'open', 'pin'], $thread)
                        <div class="col-2 pt-1">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="threadManageButton-{{ $thread->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="threadManageButton-{{ $thread->id }}">
                                    @can('update', $thread)
                                    <a href="{{ route('thread.update', ['thread' => $thread->id, 'redirect=topic.show']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $thread)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#threadDeleteModal" 
                                        onclick="$('#threadDeleteModal #deleteButton').attr('href', '{{ route('thread.delete', ['thread' => $thread->id, 'redirect=topic.show']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                    @canany(['open', 'pin'], $thread)
                                    <div class="dropdown-divider"></div>
                                    @can('open', $thread)
                                    <a href="{{ route($thread->is_open ? 'thread.close' : 'thread.open', ['thread' => $thread->id, 'redirect=topic.show']) }}" class="dropdown-item">{{ $thread->is_open ? __('Close') : __('Open') }}</a>
                                    @endcan
                                    @can('pin', $thread)
                                    <a href="{{ route($thread->is_pinned ? 'thread.unpin' : 'thread.pin', ['thread' => $thread->id, 'redirect=topic.show']) }}" class="dropdown-item">{{ $thread->is_pinned ? __('Unpin') : __('Pin') }}</a>
                                    @endcan
                                    @endcanany
                                </div>
                            </div>
                        </div>
                        @endcanany
                        @endauth
                    </div>
                    @empty
                    {{ __('Start creating some threads!') }}
                    @endforelse
                    @if ($threads->lastPage() > 1)
                    <div class="row mt-3">
                        <div class="col-12 d-flex justify-content-center">
                        {{ $threads->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Topic Delete Modal -->
<div class="modal fade" id="topicDeleteModal" tabindex="-1" role="dialog" aria-labelledby="topicDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="topicDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{ __('Do you really want to delete this topic?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteButton" href="#" class="btn btn-danger">{{ __('Delete') }}</a>
            </div>
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
@endsection
