@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="@auth @canany(['update', 'delete'], $category){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                            <div @auth @canany(['update', 'delete'], $category){!! 'class="mt-1"' !!}@endcan @endauth>
                                <a href="{{ route('index') }}">{{ __('Home') }}</a> >
                                <a href="{{ route('category.show', ['category_slug' => $category->slug]) }}">{{ $category->title }}</a>
                            </div>
                        </div>
                        @auth
                        @canany(['update', 'delete'], $category)
                        <div class="col-2">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="categoryManageButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="categoryManageButton">
                                    @can('update', $category)
                                    <a href="{{ route('category.update', ['category' => $category->id, 'redirect=category.show']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $category)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#categoryDeleteModal" 
                                        onclick="$('#categoryDeleteModal #deleteButton').attr('href', '{{ route('category.delete', ['category' => $category->id]) }}')">{{ __('Delete') }}</a>
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
                        <div class="col-12"><a href="{{ route('topic.create', ['category' => $category->id, 'redirect=category.show']) }}" class="btn btn-primary">{{ __('+ New Topic') }}</a></div>
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
                                    <a href="{{ route('topic.update', ['topic' => $topic->id, 'redirect=category.show']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $topic)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#topicDeleteModal" 
                                        onclick="$('#topicDeleteModal #deleteButton').attr('href', '{{ route('topic.delete', ['topic' => $topic->id, 'redirect=category.show']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                    @can('move', $topic)
                                    @if ($topic->order != 1 || $topic->order != $category->last_topic_order)
                                    <div class="dropdown-divider"></div>
                                    @endif
                                    @if ($topic->order != 1)
                                    <a href="{{ route('topic.move', ['topic' => $topic->id, 'dir' => 'up', 'redirect=category.show']) }}" class="dropdown-item">{{ __('Move up') }}</a>
                                    @endif
                                    @if ($topic->order != $category->last_topic_order)
                                    <a href="{{ route('topic.move', ['topic' => $topic->id, 'dir' => 'down', 'redirect=category.show']) }}" class="dropdown-item">{{ __('Move down') }}</a>
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
