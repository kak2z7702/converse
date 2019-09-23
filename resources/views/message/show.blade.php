@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="@auth @canany(['update', 'delete'], $message){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                            <div @auth @canany(['update', 'delete'], $message){!! 'class="mt-1"' !!}@endcan @endauth>
                                <a href="{{ route('message.index') }}">{{ __('Messages') }}</a> >
                                <a href="{{ route('message.show', ['message' => $message->id]) }}">{{ $message->title }}</a>
                            </div>
                        </div>
                        @auth
                        @canany(['update', 'delete'], $message)
                        <div class="col-2">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="messageManageButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="messageManageButton">
                                    @can('update', $message)
                                    <a href="{{ route('message.update', ['message' => $message->id, 'redirect=message.show']) }}" class="dropdown-item">{{ __('Edit') }}</a>
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
                </div>

                <div class="card-body">
                    {!! $message->content !!}
                    @if ($message->user != auth()->user())
                    <br />
                    <a href="{{ route('message.create', ['title' => 'Re: ' . $message->title, 'receiver' => $message->user_id]) }}" class="btn btn-primary btn-sm mt-1">{{ __('Reply To') }}</a>
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
