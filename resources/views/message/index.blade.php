@can('viewAny', 'App\Message')
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Messages') }}
                </div>

                <div class="card-body">
                    @auth
                    @can('create', 'App\Message')
                    <div class="row mb-3">
                        <div class="col-12"><a href="{{ route('message.create') }}" class="btn btn-primary">{{ __('+ New Message') }}</a></div>
                    </div>
                    @endcan
                    @endauth
                    @forelse ($messages as $message)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="col-4">
                            <h5 class="mt-2 mb-1">
                                <a href="{{ route('message.show', ['message' => $message->id]) }}">{{ $message->title }}</a> 
                                @if (!$message->is_seen && $message->receiver == auth()->user())<span class="badge badge-danger">New</span>@endif
                            </h5>
                        </div>
                        <div class="col-2">
                            <div class="mt-2">From {{ $message->user->name }}</div>
                        </div>
                        <div class="col-2">
                            <div class="mt-2">To {{ $message->receiver->name }}</div>
                        </div>
                        <div class="@auth @canany(['update', 'delete'], $message){{ 'col-2' }}@else{{ 'col-4' }}@endcanany @else{{ 'col-4' }}@endauth">
                            <div class="mt-2">{{ $message->created_at }}</div>
                        </div>
                        @auth
                        @canany(['update', 'delete'], $message)
                        <div class="col-2 pt-1">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="messageManageButton-{{ $message->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="messageManageButton-{{ $message->id }}">
                                    @can('update', $message)
                                    <a href="{{ route('message.update', ['message' => $message->id, 'redirect=message.index']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $message)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#messageDeleteModal" 
                                        onclick="$('#messageDeleteModal #deleteButton').attr('href', '{{ route('message.delete', ['message' => $message->id, 'redirect=message.index']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                        @endauth
                    </div>
                    @empty
                    {{ __('You have no messages.') }}
                    @endforelse
                    @if ($messages->hasMorePages())
                    <div class="row mt-3">
                        <div class="col-12">
                        {{ $messages->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Message Delete Modal -->
<div class="modal fade" id="messageDeleteModal" tabindex="-1" role="dialog" aria-labelledby="messageDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{ __('Do you really want to delete this message?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteButton" href="#" class="btn btn-danger">{{ __('Delete') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
@endcan