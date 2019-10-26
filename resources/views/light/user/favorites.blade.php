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
                    <a href="{{ route('user.show', ['user' => auth()->user()->id]) }}">{{ __('User') }}</a> >
                    <a href="{{ route('user.favorites', ['user' => auth()->user()->id]) }}">{{ __('Favorites') }}</a>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <button id="checkAllButton" type="button" class="btn btn-primary" onclick="checkAll()" disabled>{{ __('Check All') }}</button>
                            <button id="unfavoriteAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" onclick="unfavoriteMulti()" disabled>{{ __('Unfavorite All') }} (<span>0</span>)</button>
                        </div>
                        <div class="col-6">
                            <form action="{{ route('user.favorites', ['user' => auth()->user()->id]) }}" method="get">
                                <div class="btn-group float-right" role="group" aria-label="Search query">
                                    <input id="search" name="q" type="text" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', request()->filled('q') ? request()->q : '') }}" placeholder="Thread...">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @forelse ($threads as $thread)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="col-10">
                            <h5 class="mt-2 mb-1">
                                <input type="checkbox" class="mr-2" value="{{ $thread->id }}" onchange="trackUnfavoring(event)">
                                <a href="{{ route('thread.show', ['category_slug' => $thread->topic->category->slug, 'topic_slug' => $thread->topic->slug, 'thread_slug' => $thread->slug]) }}" target="_blank">{{ $thread->title }}</a>
                            </h5>
                        </div>
                        <div class="col-2 pt-1">
                            <a href="{{ route('thread.unfavorite', ['thread' => $thread->id, 'redirect=user.favorites']) }}" class="btn btn-warning btn-sm float-right">{{ __('Unfavorite') }}</a>
                        </div>
                    </div>
                    @empty
                    {{ __('There are no favorite threads to display.') }}
                    @endforelse
                    @if ($threads->lastPage() > 1)
                    <div class="row mt-3">
                        <div class="col-12">
                        {{ $threads->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Thread Unfavorite Form -->
<form id="threadUnfavoriteForm" action="{{ route('thread.unfavorite', ['thread' => 0]) }}" method="post" class="d-none">
    @csrf
    <input name="redirect" type="hidden" value="user.favorites" />
    <input name="threads" type="hidden" value="" />
</form>
<script>
    var checked = false;
    var unfavorited = [];
    var total = 0;

    function checkAll()
    {
        checked = !checked;
        unfavorited = [];

        $('input:checkbox').each(function (index, value) {
            $(value).prop('checked', checked);

            if (checked) unfavorited.push($(value).val());
        });

        $('#checkAllButton').text(checked ? "{{ __('Uncheck All') }}" : "{{ __('Check All') }}");
        $('#unfavoriteAllButton').prop('disabled', unfavorited.length == 0);
        $('#unfavoriteAllButton span').text(unfavorited.length);
    }

    function trackUnfavoring(event)
    {
        let key = event.target.value;
        let pos = unfavorited.indexOf(key);

        if (pos != -1) unfavorited.splice(pos, 1); else unfavorited.push(key);

        $('#unfavoriteAllButton').prop('disabled', unfavorited.length == 0);
        $('#unfavoriteAllButton span').text(unfavorited.length);

        if (unfavorited.length == 0)
        {   
            checked = false;

            $('#checkAllButton').text("{{ __('Check All') }}");
        }
        else if (unfavorited.length == total)
        {
            checked = true;

            $('#checkAllButton').text("{{ __('Uncheck All') }}");
        }
    }

    function unfavoriteMulti()
    {
        $('#threadUnfavoriteForm [name=threads]').val(unfavorited.join(','));
        $('#threadUnfavoriteForm').submit()
    }

    window.addEventListener('DOMContentLoaded', (event) => {
        total = $('input:checkbox').length;

        $('#checkAllButton').prop('disabled', total == 0);
    });
</script>
@endsection
