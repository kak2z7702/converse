@extends('layouts.app')

@section('content')
<div class="container">
    @auth
    @can('create', 'App\Category')
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('category.create') }}" class="btn btn-primary">{{ __('+ New Category') }}</a>
        </div>
    </div>
    @endcan
    @endauth
    @forelse ($categories as $category)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="@auth @canany(['update', 'delete'], $category){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                            <div @auth @canany(['update', 'delete'], $category){!! 'class="mt-1"' !!}@endcan @endauth>
                                <a href="{{ route('category.show', ['category_slug' => $category->slug]) }}">{{ $category->title }}</a>
                            </div>
                        </div>
                        @auth
                        @canany(['update', 'delete'], $category)
                        <div class="col-2">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="categoryManageButton-{{ $category->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="categoryManageButton-{{ $category->id }}">
                                    @can('update', $category)
                                    <a href="{{ route('category.update', ['category' => $category->id]) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $category)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#categoryDeleteModal" 
                                        onclick="$('#categoryDeleteModal #deleteButton').attr('href', '{{ route('category.delete', ['category' => $category->id]) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                    @can('move', $category)
                                    @if ($category->order != 1 || $category->order != $last_category_order)
                                    <div class="dropdown-divider"></div>
                                    @endif
                                    @if ($category->order != 1)
                                    <a href="{{ route('category.move', ['category' => $category->id, 'dir' => 'up']) }}" class="dropdown-item">{{ __('Move up') }}</a>
                                    @endif
                                    @if ($category->order != $last_category_order)
                                    <a href="{{ route('category.move', ['category' => $category->id, 'dir' => 'down']) }}" class="dropdown-item">{{ __('Move down') }}</a>
                                    @endif
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
                    @can('create', 'App\Topic')
                    <div class="row mb-3">
                        <div class="col-12"><a href="{{ route('topic.create', ['category' => $category->id]) }}" class="btn btn-primary">{{ __('+ New Topic') }}</a></div>
                    </div>
                    @endcan
                    @endauth
                    @forelse ($category->topics as $topic)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="@auth @canany(['update', 'delete'], $topic){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                            <a href="{{ route('topic.show', ['category_slug' => $category->slug, 'topic_slug' => $topic->slug]) }}" class="float-left mr-3 d-none d-lg-block d-xl-block">
                                <img src="@isset($topic->photo){{ asset('storage') . '/' . $topic->photo }}@else{{ asset('img/64x64.png') }}@endisset" class="rounded-circle img-border" width="64" height="64">
                            </a>
                            <a href="{{ route('topic.show', ['category_slug' => $category->slug, 'topic_slug' => $topic->slug]) }}"><h5 class="mt-2 mb-1">{{ $topic->title }}</h5></a>
                            {{ $topic->description }}
                        </div>
                        @auth
                        @canany(['update', 'delete'], $topic)
                        <div class="col-2 pt-1">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="topicManageButton-{{ $topic->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="topicManageButton-{{ $topic->id }}">
                                    @can('update', $topic)
                                    <a href="{{ route('topic.update', ['topic' => $topic->id]) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $topic)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#topicDeleteModal" 
                                        onclick="$('#topicDeleteModal #deleteButton').attr('href', '{{ route('topic.delete', ['topic' => $topic->id]) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                    @can('move', $topic)
                                    @if ($topic->order != 1 || $topic->order != $category->last_topic_order)
                                    <div class="dropdown-divider"></div>
                                    @endif
                                    @if ($topic->order != 1)
                                    <a href="{{ route('topic.move', ['topic' => $topic->id, 'dir' => 'up']) }}" class="dropdown-item">{{ __('Move up') }}</a>
                                    @endif
                                    @if ($topic->order != $category->last_topic_order)
                                    <a href="{{ route('topic.move', ['topic' => $topic->id, 'dir' => 'down']) }}" class="dropdown-item">{{ __('Move down') }}</a>
                                    @endif
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                        @endauth
                    </div>
                    @empty
                    {{ __('Start creating some topics!') }}
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @empty
    <p>{{ __('Start creating some categories!') }}</p>
    @endforelse
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">{{ __('Activity') }}</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2 text-md-right">{{ __('Today') }}</div>
                        <div class="col-md-4">{{ now()->format('Y-m-d H:i') }}</div>
                        <div class="col-md-2 text-md-right">{{ __('Born') }}</div>
                        <div class="col-md-4">{{ $options->community->birthday }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 text-md-right">{{ __('Active Thread') }}</div>
                        <div class="col-md-4">
                            @isset($activity['active_thread'])
                            <a href="{{ route('thread.show', ['category_slug' => $activity['active_thread']->topic->category->slug, 'topic_slug' => $activity['active_thread']->topic->slug, 'thread_slug' => $activity['active_thread']->slug]) }}">{{ $activity['active_thread']->title }}</a>
                            @else
                            {{ __('None') }}
                            @endisset
                        </div>
                        <div class="col-md-2 text-md-right">{{ __('Members') }}</div>
                        <div class="col-md-4">{{ $activity['members_count'] }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 text-md-right">{{ __('Active User') }}</div>
                        <div class="col-md-4">
                            @isset($activity['active_user'])
                            {{ $activity['active_user']->name }}
                            @else
                            {{ __('None') }}
                            @endisset
                        </div>
                        <div class="col-md-2 text-md-right">{{ __('Topics') }}</div>
                        <div class="col-md-4">{{ $activity['topics_count'] }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 text-md-right">{{ __('Latest Thread') }}</div>
                        <div class="col-md-4">
                            @isset($activity['latest_thread'])
                            <a href="{{ route('thread.show', ['category_slug' => $activity['latest_thread']->topic->category->slug, 'topic_slug' => $activity['latest_thread']->topic->slug, 'thread_slug' => $activity['latest_thread']->slug]) }}">{{ $activity['latest_thread']->title }}</a>
                            @else
                            {{ __('None') }}
                            @endisset
                        </div>
                        <div class="col-md-2 text-md-right">{{ __('Threads') }}</div>
                        <div class="col-md-4">{{ $activity['threads_count'] }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 text-md-right">{{ __('Latest Memeber') }}</div>
                        <div class="col-md-4">
                            @isset($activity['latest_user'])
                            {{ $activity['latest_user']->name }}
                            @else
                            {{ __('None') }}
                            @endisset
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Category Delete Modal -->
<div class="modal fade" id="categoryDeleteModal" tabindex="-1" role="dialog" aria-labelledby="categoryDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{ __('Do you really want to delete this category?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteButton" href="#" class="btn btn-danger">{{ __('Delete') }}</a>
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
@endsection
