@can('viewAny', 'App\Menu')
@extends('layouts.app')

@section('content')
<div class="container">
    @isset ($menus)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Menus') }}
                </div>

                <div class="card-body">
                    @auth
                    @can('create', 'App\Menu')
                    <div class="row mb-3">
                        <div class="col-12"><a href="{{ route('menu.create') }}" class="btn btn-primary">{{ __('+ New Menu') }}</a></div>
                    </div>
                    @endcan
                    @endauth
                    @forelse ($menus as $menu)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="col-6">
                            <a href="{{ $menu->url }}" target="_blank"><h5 class="mt-2 mb-1">{{ $menu->title }}</h5></a>
                        </div>
                        <div class="@auth @canany(['update', 'delete'], $menu){{ 'col-4' }}@else{{ 'col-6' }}@endcanany @else{{ 'col-6' }}@endauth">
                            <div class="mt-2">{{ $menu->url }}</div>
                        </div>
                        @auth
                        @canany(['update', 'delete'], $menu)
                        <div class="col-2 pt-1">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="menuManageButton-{{ $menu->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Manage</button>
                                <div class="dropdown-menu" aria-labelledby="menuManageButton-{{ $menu->id }}">
                                    @can('update', $menu)
                                    <a href="{{ route('menu.update', ['menu' => $menu->id]) }}" class="dropdown-item">Edit</a>
                                    @endcan
                                    @can('delete', $menu)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#menuDeleteModal" 
                                        onclick="$('#menuDeleteModal #deleteButton').attr('href', '{{ route('menu.delete', ['menu' => $menu->id]) }}')">Delete</a>
                                    @endcan
                                    @can('move', $menu)
                                    @if ($menu->order != 1 || $menu->order != $last_menu_order)
                                    <div class="dropdown-divider"></div>
                                    @endif
                                    @if ($menu->order != 1)
                                    <a href="{{ route('menu.move', ['menu' => $menu->id, 'dir' => 'up']) }}" class="dropdown-item">Move up</a>
                                    @endif
                                    @if ($menu->order != $last_menu_order)
                                    <a href="{{ route('menu.move', ['menu' => $menu->id, 'dir' => 'down']) }}" class="dropdown-item">Move down</a>
                                    @endif
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                        @endauth
                    </div>
                    @empty
                    {{ __('This community has no menus.') }}
                    @endforelse
                    @if ($menus->hasMorePages())
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
    @endisset
</div>
<!-- Menu Delete Modal -->
<div class="modal fade" id="menuDeleteModal" tabindex="-1" role="dialog" aria-labelledby="menuDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuDeleteModal">Are you sure?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                Do you really want to delete this menu?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a id="deleteButton" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>
@endsection
@endcan
