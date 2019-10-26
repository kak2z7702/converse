@can('viewAny', 'App\User')
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
                    <a href="{{ route('index') }}">{{ __('Home') }}</a> >
                    <a href="{{ route('user.index') }}">{{ __('Users') }}</a>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        @can('create', 'App\User')
                        <div class="col-6">
                            <a href="{{ route('user.create', ['redirect=user.index']) }}" class="btn btn-primary">{{ __('+ New User') }}</a>
                            <button id="checkAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" onclick="checkAll()" disabled>{{ __('Check All') }}</button>
                            <button id="deleteAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" data-toggle="modal" data-target="#userDeleteModal" onclick="deleteMulti()" disabled>{{ __('Delete All') }} (<span>0</span>)</button>
                        </div>
                        @endcan
                        <div class="@can('create', 'App\User'){{ 'col-6' }}@else{{ 'col-12' }}@endcan">
                            <form action="{{ route('user.index') }}" method="get">
                                <div class="btn-group float-right" role="group" aria-label="Search query">
                                    <input id="search" name="q" type="text" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', request()->filled('q') ? request()->q : '') }}" placeholder="Name...">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @forelse ($users as $user)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="@canany(['update', 'delete'], $user){{ 'col-10' }}@else{{ 'col-12' }}@endcanany">
                            <h5 class="mt-2 mb-1">
                                @can('delete', $user)
                                <input type="checkbox" class="mr-2" value="{{ $user->id }}" onchange="trackDeletion(event)">
                                @endcan
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
                                        onclick="deleteSingle('{{ route('user.delete', ['user' => $user->id, 'redirect=user.index']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                    @can('ban', $user)
                                    <div class="dropdown-divider"></div>
                                    <a href="{{ route($user->is_banned ? 'user.unban' : 'user.ban', ['user' => $user->id, 'redirect=user.index']) }}" class="dropdown-item">{{ $user->is_banned ? __('Unban') : __('Ban') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                    </div>
                    @empty
                    {{ __('There are no users to display.') }}
                    @endforelse
                    @if ($users->lastPage() > 1)
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
<!-- User Delete Form -->
<form id="userDeleteForm" action="{{ route('user.delete') }}" method="post" class="d-none">
    @csrf
    <input name="users" type="hidden" value="" />
</form>
<!-- User Delete Modal -->
<div class="modal fade" id="userDeleteModal" tabindex="-1" role="dialog" aria-labelledby="userDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div id="singularMessage" class="modal-body d-none">
                {{ __('Do you really want to delete this user?') }}
            </div>
            <div id="poluralMessage" class="modal-body d-none">
                {{ __('Do you really want to delete these users?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteSingleButton" href="#" class="btn btn-danger d-none">{{ __('Delete') }}</a>
                <button id="deleteMultiButton" class="btn btn-danger d-none" onclick="$('#userDeleteForm').submit()">{{ __('Delete') }}</button>
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
        $('#userDeleteModal #singularMessage').removeClass('d-none');
        $('#userDeleteModal #poluralMessage').addClass('d-none');
        $('#userDeleteModal #deleteSingleButton').removeClass('d-none');
        $('#userDeleteModal #deleteMultiButton').addClass('d-none');
        $('#userDeleteModal #deleteSingleButton').attr('href', href);
    }

    function deleteMulti()
    {
        $('#userDeleteModal #singularMessage').addClass('d-none');
        $('#userDeleteModal #poluralMessage').removeClass('d-none');
        $('#userDeleteModal #deleteSingleButton').addClass('d-none');
        $('#userDeleteModal #deleteMultiButton').removeClass('d-none');
        $('#userDeleteForm [name=users]').val(deleted.join(','));
    }

    window.addEventListener('DOMContentLoaded', (event) => {
        total = $('input:checkbox').length;

        $('#checkAllButton').prop('disabled', total == 0);
    });
</script>
@endsection
@endcan