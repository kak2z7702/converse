@can('viewAny', 'App\Role')
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
                    {{ __('Roles') }}
                </div>

                <div class="card-body">
                    @can('create', 'App\Role')
                    <div class="row mb-3">
                        <div class="col-5"><a href="{{ route('role.create') }}" class="btn btn-primary">{{ __('+ New Role') }}</a></div>
                        <div class="col-7">
                            <form action="{{ route('role.index') }}" method="get">
                                <div class="btn-group float-right" role="group" aria-label="Search query">
                                    <input id="search" name="q" type="text" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', request()->filled('q') ? request()->q : '') }}" placeholder="Title..." autofocus>
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endcan
                    @forelse ($roles as $role)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="@canany(['update', 'delete'], $role){{ 'col-10' }}@else{{ 'col-12' }}@endcanany">
                            <h5 class="mt-2 mb-1">{{ $role->title }} @if ($role->is_protected)<span class="badge badge-info">{{ __('Protected') }}</span>@endif</h5>
                        </div>
                        @canany(['update', 'delete'], $role)
                        <div class="col-2 pt-1">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="roleManageButton-{{ $role->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="roleManageButton-{{ $role->id }}">
                                    @can('update', $role)
                                    <a href="{{ route('role.update', ['role' => $role->id]) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $role)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#roleDeleteModal" 
                                        onclick="$('#roleDeleteModal #deleteButton').attr('href', '{{ route('role.delete', ['role' => $role->id]) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                    </div>
                    @empty
                    {{ __('There are no roles to display.') }}
                    @endforelse
                    @if ($roles->lastPage() > 1)
                    <div class="row mt-3">
                        <div class="col-12">
                        {{ $roles->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Role Delete Modal -->
<div class="modal fade" id="roleDeleteModal" tabindex="-1" role="dialog" aria-labelledby="roleDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{ __('Do you really want to delete this role?') }}
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