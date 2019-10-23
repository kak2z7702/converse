@can('viewAny', 'App\Page')
@extends(config('theme.layout'))

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Pages') }}
                </div>

                <div class="card-body">
                    @auth
                    @can('create', 'App\Page')
                    <div class="row mb-3">
                        <div class="col-12"><a href="{{ route('page.create') }}" class="btn btn-primary">{{ __('+ New Page') }}</a></div>
                    </div>
                    @endcan
                    @endauth
                    @forelse ($pages as $page)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="@auth @canany(['update', 'delete'], $page){{ 'col-10' }}@else{{ 'col-12' }}@endcanany @else{{ 'col-12' }}@endauth">
                            <a href="{{ route('page.show', ['slug' => $page->slug]) }}" target="_blank"><h5 class="mt-2 mb-1">{{ $page->title }}</h5></a>
                        </div>
                        @auth
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
                                        onclick="$('#pageDeleteModal #deleteButton').attr('href', '{{ route('page.delete', ['page' => $page->id, 'redirect=page.index']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                        @endauth
                    </div>
                    @empty
                    {{ __('This community has no pages.') }}
                    @endforelse
                    @if ($pages->hasMorePages())
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
<!-- Page Delete Modal -->
<div class="modal fade" id="pageDeleteModal" tabindex="-1" role="dialog" aria-labelledby="pageDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{ __('Do you really want to delete this page?') }}
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