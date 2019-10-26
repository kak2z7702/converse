@can('viewAny', 'App\Message')
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
                    <a href="{{ route('message.index') }}">{{ __('Messages') }}</a>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        @can('create', 'App\Message')
                        <div class="col-6">
                            <a href="{{ route('message.create') }}" class="btn btn-primary">{{ __('+ New Message') }}</a>
                            <button id="checkAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" onclick="checkAll()" disabled>{{ __('Check All') }}</button>
                            <button id="deleteAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" data-toggle="modal" data-target="#messageDeleteModal" onclick="deleteMulti()" disabled>{{ __('Delete All') }} (<span>0</span>)</button>
                        </div>
                        @endcan
                        <div class="@can('create', 'App\Message'){{ 'col-6' }}@else{{ 'col-12' }}@endcan">
                            <form action="{{ route('message.index') }}" method="get">
                                <div class="btn-group float-right" role="group" aria-label="Search query">
                                    <input id="search" name="q" type="text" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', request()->filled('q') ? request()->q : '') }}" placeholder="Title...">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @forelse ($messages as $message)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="col-4">
                            <h5 class="mt-2 mb-1">
                                @can('delete', $message)
                                <input type="checkbox" class="mr-2" value="{{ $message->id }}" onchange="trackDeletion(event)">
                                @endcan
                                <a href="{{ route('message.show', ['message' => $message->id]) }}">{{ $message->title }}</a> 
                                @if (!$message->is_seen && $message->receiver == auth()->user())<span class="badge badge-danger">New</span>@endif
                            </h5>
                        </div>
                        <div class="col-2">
                            <div class="mt-2">From <a href="{{ route('user.show', ['user' => $message->user->id]) }}">{{ $message->user->name }}</a></div>
                        </div>
                        <div class="col-2">
                            <div class="mt-2">To <a href="{{ route('user.show', ['user' => $message->receiver->id]) }}">{{ $message->receiver->name }}</a></div>
                        </div>
                        <div class="@canany(['update', 'delete'], $message){{ 'col-2' }}@else{{ 'col-4' }}@endcanany">
                            <div class="mt-2">{{ $message->created_at }}</div>
                        </div>
                        @canany(['update', 'delete'], $message)
                        <div class="col-2 pt-1">
                            <div class="dropdown float-right">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="messageManageButton-{{ $message->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Manage') }}</button>
                                <div class="dropdown-menu" aria-labelledby="messageManageButton-{{ $message->id }}">
                                    @can('update', $message)
                                    <a href="{{ route('message.update', ['message' => $message->id, 'redirect=message.index']) }}" class="dropdown-item">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $message)
                                    <a href="#" class="dropdown-item" 
                                        data-toggle="modal" data-target="#messageDeleteModal" 
                                        onclick="deleteSingle('{{ route('message.delete', ['message' => $message->id, 'redirect=message.index']) }}')">{{ __('Delete') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany
                    </div>
                    @empty
                    {{ __('There are no messages to display.') }}
                    @endforelse
                    @if ($messages->lastPage() > 1)
                    <div class="row mt-3">
                        <div class="col-12">
                        {{ $messages->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Message Delete Form -->
<form id="messageDeleteForm" action="{{ route('message.delete', ['message' => 0]) }}" method="post" class="d-none">
    @csrf
    <input name="messages" type="hidden" value="" />
</form>
<!-- Message Delete Modal -->
<div class="modal fade" id="messageDeleteModal" tabindex="-1" role="dialog" aria-labelledby="messageDeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageDeleteModal">{{ __('Are you sure?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div id="singularMessage" class="modal-body d-none">
                {{ __('Do you really want to delete this message?') }}
            </div>
            <div id="poluralMessage" class="modal-body d-none">
                {{ __('Do you really want to delete these messages?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a id="deleteSingleButton" href="#" class="btn btn-danger d-none">{{ __('Delete') }}</a>
                <button id="deleteMultiButton" class="btn btn-danger d-none" onclick="$('#messageDeleteForm').submit()">{{ __('Delete') }}</button>
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
        $('#messageDeleteModal #singularMessage').removeClass('d-none');
        $('#messageDeleteModal #poluralMessage').addClass('d-none');
        $('#messageDeleteModal #deleteSingleButton').removeClass('d-none');
        $('#messageDeleteModal #deleteMultiButton').addClass('d-none');
        $('#messageDeleteModal #deleteSingleButton').attr('href', href);
    }

    function deleteMulti()
    {
        $('#messageDeleteModal #singularMessage').addClass('d-none');
        $('#messageDeleteModal #poluralMessage').removeClass('d-none');
        $('#messageDeleteModal #deleteSingleButton').addClass('d-none');
        $('#messageDeleteModal #deleteMultiButton').removeClass('d-none');
        $('#messageDeleteForm [name=messages]').val(deleted.join(','));
    }

    window.addEventListener('DOMContentLoaded', (event) => {
        total = $('input:checkbox').length;

        $('#checkAllButton').prop('disabled', total == 0);
    });
</script>
@endsection
@endcan