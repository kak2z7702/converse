@extends('layouts.app')

@section('content')
<div class="container">
    @auth
    @canany(['update', 'delete'], $page)
    <div class="dropdown">
        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="pageManageButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Manage</button>
        <div class="dropdown-menu" aria-labelledby="pageManageButton">
            @can('update', $page)
            <a href="{{ route('page.update', ['page' => $page->id, 'redirect=page.show']) }}" class="dropdown-item">Edit</a>
            @endcan
            @can('delete', $page)
            <a href="#" class="dropdown-item" 
                data-toggle="modal" data-target="#pageDeleteModal" 
                onclick="$('#pageDeleteModal #deleteButton').attr('href', '{{ route('page.delete', ['page' => $page->id]) }}')">Delete</a>
            @endcan
        </div>
    </div>
    @endcanany
    @endauth
    <h1 class="mt-3">{{ $page->title }}</h1>
    <p>{!! $page->content !!}</p>
</div>
<!-- Page Delete Modal -->
<div class="modal fade" id="pageDeleteModal" tabindex="-1" role="dialog" aria-labelledby="pageDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageDeleteModal">Are you sure?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                Do you really want to delete this page?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a id="deleteButton" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>
@endsection
