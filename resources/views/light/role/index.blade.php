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
                    <div class="row mb-3">
                        @can('create', 'App\Role')
                        <div class="col-6">
                            <a href="{{ route('role.create') }}" class="btn btn-primary">{{ __('+ New Role') }}</a>
                            <button id="checkAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" onclick="checkAll()" disabled>{{ __('Check All') }}</button>
                            <button id="deleteAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" data-toggle="modal" data-target="#roleDeleteModal" onclick="deleteMulti()" disabled>{{ __('Delete All') }} (<span>0</span>)</button>
                        </div>
                        @endcan
                        <div class="@can('create', 'App\Role'){{ 'col-6' }}@else{{ 'col-12' }}@endcan">
                            <form action="{{ route('role.index') }}" method="get">
                                <div class="btn-group float-right" role="group" aria-label="Search query">
                                    <input id="search" name="q" type="text" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', request()->filled('q') ? request()->q : '') }}" placeholder="Title...">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @forelse ($roles as $role)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="@canany(['update', 'delete'], $role){{ 'col-10' }}@else{{ 'col-12' }}@endcanany">
                            <h5 class="mt-2 mb-1">
                                @can('delete', $role)
                                <input type="checkbox" class="mr-2" value="{{ $role->id }}" onchange="trackDeletion(event)">
                                @endcan
                                {{ $role->title }} 
                                @if ($role->is_protected)
                                <span class="badge badge-info">{{ __('Protected') }}</span>
                                @endif
                            </h5>
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
                                        onclick="deleteSingle('{{ route('role.delete', ['role' => $role->id]) }}')">{{ __('Delete') }}</a>
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
<!-- Role Delete Form -->
<form id="roleDeleteForm" action="{{ route('role.delete') }}" method="post" class="d-none">
    @csrf
    <input name="roles" type="hidden" value="" />
</form>
<!-- Role Delete Modal -->
<div class="modal fade" id="roleDeleteModal" tabindex="-1" role="dialog" aria-labelledby="roleDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div id="singularMessage" class="modal-body d-none">
                {{ __('Do you really want to delete this role?') }}
            </div>
            <div id="poluralMessage" class="modal-body d-none">
                {{ __('Do you really want to delete these roles?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteSingleButton" href="#" class="btn btn-danger d-none">{{ __('Delete') }}</a>
                <button id="deleteMultiButton" class="btn btn-danger d-none" onclick="$('#roleDeleteForm').submit()">{{ __('Delete') }}</button>
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
        $('#roleDeleteModal #singularMessage').removeClass('d-none');
        $('#roleDeleteModal #poluralMessage').addClass('d-none');
        $('#roleDeleteModal #deleteSingleButton').removeClass('d-none');
        $('#roleDeleteModal #deleteMultiButton').addClass('d-none');
        $('#roleDeleteModal #deleteSingleButton').attr('href', href);
    }

    function deleteMulti()
    {
        $('#roleDeleteModal #singularMessage').addClass('d-none');
        $('#roleDeleteModal #poluralMessage').removeClass('d-none');
        $('#roleDeleteModal #deleteSingleButton').addClass('d-none');
        $('#roleDeleteModal #deleteMultiButton').removeClass('d-none');
        $('#roleDeleteForm [name=roles]').val(deleted.join(','));
    }

    window.addEventListener('DOMContentLoaded', (event) => {
        total = $('input:checkbox').length;

        $('#checkAllButton').prop('disabled', total == 0);
    });
</script>
@endsection
@endcan