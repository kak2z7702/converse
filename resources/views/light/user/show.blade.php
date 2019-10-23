@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="@auth @canany(['update', 'delete'], $user){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                            <div @auth @canany(['update', 'delete'], $user){!! 'class="mt-1"' !!}@endcan @endauth>
                                <a href="{{ route('index') }}">{{ __('Home') }}</a> > 
                                <a href="{{ route('user.index') }}">{{ __('Users') }}</a> > 
                                <a href="{{ route('user.show', ['user' => $user->id]) }}">{{ $user->name }}</a>
                            </div>
                        </div>
                        @auth
                        @canany(['update', 'delete', 'ban'], $user)
                        <div class="col-2">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="userManageButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="userManageButton">
                                    @can('update', $user)
                                    <a href="{{ route('user.update', ['user' => $user->id, 'redirect=user.show']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $user)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#userDeleteModal" 
                                        onclick="$('#userDeleteModal #deleteButton').attr('href', '{{ route('user.delete', ['user' => $user->id, 'redirect=user.index']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                    @can('ban', $user)
                                    <div class="dropdown-divider"></div>
                                    <a href="{{ route($user->is_banned ? 'user.unban' : 'user.ban', ['user' => $user->id, 'redirect=user.show']) }}" class="dropdown-item">{{ $user->is_banned ? __('Unban') : __('Ban') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                        @endauth
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="@isset($user->photo){{ asset('storage') . '/' . $user->photo }}@else{{ asset('img/64x64.png') }}@endisset" class="rounded-circle img-border float-md-right mb-2" width="64" height="64">
                        </div>
                        <div class="col-md-8">
                            <h1>{{ $user->name }}</h1>
                            <h6>
                                @if ($user->is_admin)
                                <span class="badge badge-info align-baseline">{{ __('Admin') }}</span>
                                @elseif ($user->badge && $user->badge != 'None')
                                <span class="badge badge-info align-baseline">{{ __($user->badge) }}</span>
                                @endif
                            </h6>
                            <small>{{ __('Member Since') }} {{ $user->created_at->format('Y-m-d') }}</small>
                        </div>
                    </div>
                    @if ($user->show_email)
                    <div class="row mt-1">
                        <div class="col-md-8 offset-md-2">
                            {{ $user->email }}
                        </div>
                    </div>
                    @endif
                    @if ($user->bio)
                    <div class="row mt-1">
                        <div class="col-md-8 offset-md-2">
                            {{ $user->bio }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- User Delete Modal -->
<div class="modal fade" id="userDeleteModal" tabindex="-1" role="dialog" aria-labelledby="userDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{ __('Do you really want to delete this user?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteButton" href="#" class="btn btn-danger">{{ __('Delete') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
