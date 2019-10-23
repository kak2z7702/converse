@can('viewAny', 'App\User')
@extends(config('theme.layout'))

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Users') }}
                </div>

                <div class="card-body">
                    @auth
                    @can('create', 'App\User')
                    <div class="row mb-3">
                        <div class="col-12"><a href="{{ route('user.create', ['redirect=user.index']) }}" class="btn btn-primary">{{ __('+ New User') }}</a></div>
                    </div>
                    @endcan
                    @endauth
                    @forelse ($users as $user)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="@auth @canany(['update', 'delete'], $user){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                            <h5 class="mt-2 mb-1">
                                <a href="{{ route('user.show', ['user' => $user->id]) }}">{{ $user->name }}</a>
                                @if ($user->is_admin)
                                <span class="badge badge-info">{{ __('Admin') }}</span>
                                @elseif ($user->badge && $user->badge != 'None')
                                <span class="badge badge-info">{{ __($user->badge) }}</span>
                                @endif
                                @if ($user->is_banned)
                                <span class="badge badge-danger">{{ __('Banned') }}</span>
                                @endif
                            </h5>
                        </div>
                        @auth
                        @canany(['update', 'delete', 'ban'], $user)
                        <div class="col-2 pt-1">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="userManageButton-{{ $user->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="userManageButton-{{ $user->id }}">
                                    @can('update', $user)
                                    <a href="{{ route('user.update', ['user' => $user->id, 'redirect=user.index']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $user)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#userDeleteModal" 
                                        onclick="$('#userDeleteModal #deleteButton').attr('href', '{{ route('user.delete', ['user' => $user->id, 'redirect=user.index']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                    @can('ban', $user)
                                    <div class="dropdown-divider"></div>
                                    <a href="{{ route($user->is_banned ? 'user.unban' : 'user.ban', ['user' => $user->id, 'redirect=user.index']) }}" class="dropdown-item">{{ $user->is_banned ? __('Unban') : __('Ban') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                        @endauth
                    </div>
                    @empty
                    {{ __('This community has no users.') }}
                    @endforelse
                    @if ($users->hasMorePages())
                    <div class="row mt-3">
                        <div class="col-12">
                        {{ $users->links() }}
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
@endcan