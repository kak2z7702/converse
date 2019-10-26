@can('viewAny', 'App\Page')
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
                    {{ __('Pages') }}
                </div>
                <div class="card-body">                    
                    <div class="row mb-3">
                        @can('create', 'App\Page')
                        <div class="col-6">
                            <a href="{{ route('page.create') }}" class="btn btn-primary">{{ __('+ New Page') }}</a>
                            <button id="checkAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" onclick="checkAll()" disabled>{{ __('Check All') }}</button>
                            <button id="deleteAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" data-toggle="modal" data-target="#pageDeleteModal" onclick="deleteMulti()" disabled>{{ __('Delete All') }} (<span>0</span>)</button>
                        </div>
                        @endcan
                        <div class="@can('create', 'App\Page'){{ 'col-6' }}@else{{ 'col-12' }}@endcan">
                            <form action="{{ route('page.index') }}" method="get">
                                <div class="btn-group float-right" role="group" aria-label="Search query">
                                    <input id="search" name="q" type="text" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', request()->filled('q') ? request()->q : '') }}" placeholder="Title...">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @forelse ($pages as $page)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="@canany(['update', 'delete'], $page){{ 'col-10' }}@else{{ 'col-12' }}@endcanany">    
                            <h5 class="mt-2 mb-1">
                                @can('delete', $page)
                                <input type="checkbox" class="mr-2" value="{{ $page->id }}" onchange="trackDeletion(event)">
                                @endcan
                                <a href="{{ route('page.show', ['slug' => $page->slug]) }}" target="_blank">{{ $page->title }}</a>
                            </h5>
                        </div>
                        @canany(['update', 'delete'], $page)
                        <div class="col-2 pt-1">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="pageManageButton-{{ $page->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="pageManageButton-{{ $page->id }}">
                                    @can('update', $page)
                                    <a href="{{ route('page.update', ['page' => $page->id, 'redirect=page.index']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $page)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#pageDeleteModal" 
                                        onclick="deleteSingle('{{ route('page.delete', ['page' => $page->id, 'redirect=page.index']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                    </div>
                    @empty
                    {{ __('There are no pages to display.') }}
                    @endforelse
                    @if ($pages->lastPage() > 1)
                    <div class="row mt-3">
                        <div class="col-12">
                        {{ $pages->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Page Delete Form -->
<form id="pageDeleteForm" action="{{ route('page.delete') }}" method="post" class="d-none">
    @csrf
    <input name="pages" type="hidden" value="" />
</form>
<!-- Page Delete Modal -->
<div class="modal fade" id="pageDeleteModal" tabindex="-1" role="dialog" aria-labelledby="pageDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div id="singularMessage" class="modal-body d-none">
                {{ __('Do you really want to delete this page?') }}
            </div>
            <div id="poluralMessage" class="modal-body d-none">
                {{ __('Do you really want to delete these pages?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteSingleButton" href="#" class="btn btn-danger d-none">{{ __('Delete') }}</a>
                <button id="deleteMultiButton" class="btn btn-danger d-none" onclick="$('#pageDeleteForm').submit()">{{ __('Delete') }}</button>
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
        $('#pageDeleteModal #singularMessage').removeClass('d-none');
        $('#pageDeleteModal #poluralMessage').addClass('d-none');
        $('#pageDeleteModal #deleteSingleButton').removeClass('d-none');
        $('#pageDeleteModal #deleteMultiButton').addClass('d-none');
        $('#pageDeleteModal #deleteSingleButton').attr('href', href);
    }

    function deleteMulti()
    {
        $('#pageDeleteModal #singularMessage').addClass('d-none');
        $('#pageDeleteModal #poluralMessage').removeClass('d-none');
        $('#pageDeleteModal #deleteSingleButton').addClass('d-none');
        $('#pageDeleteModal #deleteMultiButton').removeClass('d-none');
        $('#pageDeleteForm [name=pages]').val(deleted.join(','));
    }

    window.addEventListener('DOMContentLoaded', (event) => {
        total = $('input:checkbox').length;

        $('#checkAllButton').prop('disabled', total == 0);
    });
</script>
@endsection
@endcan