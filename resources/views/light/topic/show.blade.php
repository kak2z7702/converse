@extends(config('theme.layout'))

@section('css')
#search {
    -webkit-border-top-right-radius: 0 !important;
    -webkit-border-bottom-right-radius: 0 !important;
    -moz-border-radius-topright: 0 !important;
    -moz-border-radius-bottomright: 0 !important;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    min-width: 50px;
}
@endsection

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
                    <div class="row mb-3">
                        @can('create', 'App\Thread')
                        <div class="col-6">
                            <a href="{{ route('thread.create', ['topic' => $topic->id, 'redirect=topic.show']) }}" class="btn btn-primary">{{ __('+ New Thread') }}</a>
                            <button id="checkAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" onclick="checkAll()" disabled>{{ __('Check All') }}</button>
                            <button id="deleteAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" data-toggle="modal" data-target="#threadDeleteModal" onclick="deleteMulti()" disabled>{{ __('Delete All') }} (<span>0</span>)</button>
                        </div>
                        @endcan
                        <div class="@can('create', 'App\Thread'){{ 'col-6' }}@else{{ 'col-12' }}@endcan">
                            <form action="{{ route('topic.show', ['category_slug' => $topic->category->slug, 'topic_slug' => $topic->slug]) }}" method="get">
                                <div class="btn-group float-right" role="group" aria-label="Search query">
                                    <input id="search" name="q" type="text" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', request()->filled('q') ? request()->q : '') }}" placeholder="Thread...">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endauth
                    @forelse ($threads as $thread)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="col-8">
                            <h5 class="mt-2 mb-1">
                                @can('delete', $thread)
                                <input type="checkbox" class="mr-2" value="{{ $thread->id }}" onchange="trackDeletion(event)">
                                @endcan
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
                                        onclick="deleteSingle('{{ route('thread.delete', ['thread' => $thread->id, 'redirect=topic.show']) }}')">{{ __('Delete') }}</a>
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
                    {{ __('There are no threads to display.') }}
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
<!-- Thread Delete Form -->
<form id="threadDeleteForm" action="{{ route('thread.delete') }}" method="post" class="d-none">
    @csrf
    <input name="redirect" type="hidden" value="topic.show" />
    <input name="threads" type="hidden" value="" />
</form>
<!-- Thread Delete Modal -->
<div class="modal fade" id="threadDeleteModal" tabindex="-1" role="dialog" aria-labelledby="threadDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="threadDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div id="singularMessage" class="modal-body d-none">
                {{ __('Do you really want to delete this thread?') }}
            </div>
            <div id="poluralMessage" class="modal-body d-none">
                {{ __('Do you really want to delete these threads?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteSingleButton" href="#" class="btn btn-danger d-none">{{ __('Delete') }}</a>
                <button id="deleteMultiButton" class="btn btn-danger d-none" onclick="$('#threadDeleteForm').submit()">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
<script>
    var checked = false;
    var deleted = [];
    var total = 0;

    function checkAll()
    {
        checked = !checked;
        deleted = [];

        $('input:checkbox').each(function (index, value) {
            $(value).prop('checked', checked);

            if (checked) deleted.push($(value).val());
        });

        $('#checkAllButton').text(checked ? "{{ __('Uncheck All') }}" : "{{ __('Check All') }}");
        $('#deleteAllButton').prop('disabled', deleted.length == 0);
        $('#deleteAllButton span').text(deleted.length);
    }

    function trackDeletion(event)
    {
        let key = event.target.value;
        let pos = deleted.indexOf(key);

        if (pos != -1) deleted.splice(pos, 1); else deleted.push(key);

        $('#deleteAllButton').prop('disabled', deleted.length == 0);
        $('#deleteAllButton span').text(deleted.length);

        if (deleted.length == 0)
        {   
            checked = false;

            $('#checkAllButton').text("{{ __('Check All') }}");
        }
        else if (deleted.length == total)
        {
            checked = true;

            $('#checkAllButton').text("{{ __('Uncheck All') }}");
        }
    }

    function deleteSingle(href)
    {
        $('#threadDeleteModal #singularMessage').removeClass('d-none');
        $('#threadDeleteModal #poluralMessage').addClass('d-none');
        $('#threadDeleteModal #deleteSingleButton').removeClass('d-none');
        $('#threadDeleteModal #deleteMultiButton').addClass('d-none');
        $('#threadDeleteModal #deleteSingleButton').attr('href', href);
    }

    function deleteMulti()
    {
        $('#threadDeleteModal #singularMessage').addClass('d-none');
        $('#threadDeleteModal #poluralMessage').removeClass('d-none');
        $('#threadDeleteModal #deleteSingleButton').addClass('d-none');
        $('#threadDeleteModal #deleteMultiButton').removeClass('d-none');
        $('#threadDeleteForm [name=threads]').val(deleted.join(','));
    }

    window.addEventListener('DOMContentLoaded', (event) => {
        total = $('input:checkbox').length;

        $('#checkAllButton').prop('disabled', total == 0);
    });
</script>
@endsection
