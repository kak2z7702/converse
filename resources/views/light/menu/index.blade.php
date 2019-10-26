@can('viewAny', 'App\Menu')
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
                    {{ __('Menus') }}
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        @can('create', 'App\Menu')
                        <div class="col-6">
                            <a href="{{ route('menu.create') }}" class="btn btn-primary">{{ __('+ New Menu') }}</a>
                            <button id="checkAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" onclick="checkAll()" disabled>{{ __('Check All') }}</button>
                            <button id="deleteAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" data-toggle="modal" data-target="#menuDeleteModal" onclick="deleteMulti()" disabled>{{ __('Delete All') }} (<span>0</span>)</button>
                        </div>
                        @endcan
                        <div class="@can('create', 'App\Menu'){{ 'col-6' }}@else{{ 'col-12' }}@endcan">
                            <form action="{{ route('menu.index') }}" method="get">
                                <div class="btn-group float-right" role="group" aria-label="Search query">
                                    <input id="search" name="q" type="text" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', request()->filled('q') ? request()->q : '') }}" placeholder="Title...">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @forelse ($menus as $menu)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="col-6">
                            <h5 class="mt-2 mb-1">
                                <input type="checkbox" class="mr-2" value="{{ $menu->id }}" onchange="trackDeletion(event)">
                                <a href="{{ $menu->url }}" target="_blank">{{ $menu->title }}</a>
                            </h5>
                        </div>
                        <div class="@canany(['update', 'delete'], $menu){{ 'col-4' }}@else{{ 'col-6' }}@endcanany">
                            <div class="mt-2">{{ $menu->url }}</div>
                        </div>
                        @canany(['update', 'delete', 'move'], $menu)
                        <div class="col-2 pt-1">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="menuManageButton-{{ $menu->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="menuManageButton-{{ $menu->id }}">
                                    @can('update', $menu)
                                    <a href="{{ route('menu.update', ['menu' => $menu->id]) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $menu)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#menuDeleteModal" 
                                        onclick="$('#menuDeleteModal #deleteButton').attr('href', '{{ route('menu.delete', ['menu' => $menu->id]) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                    @can('move', $menu)
                                    @if ($menu->order != 1 || $menu->order != $last_menu_order)
                                    <div class="dropdown-divider"></div>
                                    @endif
                                    @if ($menu->order != 1)
                                    <a href="{{ route('menu.move', ['menu' => $menu->id, 'dir' => 'up']) }}" class="dropdown-item">{{ __('Move up') }}</a>
                                    @endif
                                    @if ($menu->order != $last_menu_order)
                                    <a href="{{ route('menu.move', ['menu' => $menu->id, 'dir' => 'down']) }}" class="dropdown-item">{{ __('Move down') }}</a>
                                    @endif
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                    </div>
                    @empty
                    {{ __('There are no menus to display.') }}
                    @endforelse
                    @if ($menus->lastPage() > 1)
                    <div class="row mt-3">
                        <div class="col-12">
                        {{ $menus->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Menu Delete Form -->
<form id="menuDeleteForm" action="{{ route('menu.delete') }}" method="post" class="d-none">
    @csrf
    <input name="menus" type="hidden" value="" />
</form>
<!-- Menu Delete Modal -->
<div class="modal fade" id="menuDeleteModal" tabindex="-1" role="dialog" aria-labelledby="menuDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div id="singularMessage" class="modal-body d-none">
                {{ __('Do you really want to delete this menu?') }}
            </div>
            <div id="poluralMessage" class="modal-body d-none">
                {{ __('Do you really want to delete these menus?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteSingleButton" href="#" class="btn btn-danger d-none">{{ __('Delete') }}</a>
                <button id="deleteMultiButton" class="btn btn-danger d-none" onclick="$('#menuDeleteForm').submit()">{{ __('Delete') }}</button>
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
        $('#menuDeleteModal #singularMessage').removeClass('d-none');
        $('#menuDeleteModal #poluralMessage').addClass('d-none');
        $('#menuDeleteModal #deleteSingleButton').removeClass('d-none');
        $('#menuDeleteModal #deleteMultiButton').addClass('d-none');
        $('#menuDeleteModal #deleteButton').attr('href', href);
    }

    function deleteMulti()
    {
        $('#menuDeleteModal #singularMessage').addClass('d-none');
        $('#menuDeleteModal #poluralMessage').removeClass('d-none');
        $('#menuDeleteModal #deleteSingleButton').addClass('d-none');
        $('#menuDeleteModal #deleteMultiButton').removeClass('d-none');
        $('#menuDeleteForm [name=menus]').val(deleted.join(','));
    }

    window.addEventListener('DOMContentLoaded', (event) => {
        total = $('input:checkbox').length;

        $('#checkAllButton').prop('disabled', total == 0);
    });
</script>
@endsection
@endcan
