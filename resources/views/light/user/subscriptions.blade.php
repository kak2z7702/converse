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
                    <a href="{{ route('user.subscriptions', ['user' => auth()->user()->id]) }}">{{ __('Subscriptions') }}</a>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <button id="checkAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" onclick="checkAll()" disabled>{{ __('Check All') }}</button>
                            <button id="unsubscribeAllButton" type="button" class="btn btn-primary mt-2 mt-md-0" onclick="unsubscribeMulti()" disabled>{{ __('Unsubscribe All') }} (<span>0</span>)</button>
                        </div>
                        <div class="col-6">
                            <form action="{{ route('user.subscriptions', ['user' => auth()->user()->id]) }}" method="get">
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
                                <input type="checkbox" class="mr-2" value="{{ $thread->id }}" onchange="trackUnsubscribing(event)">
                                <a href="{{ route('thread.show', ['category_slug' => $thread->topic->category->slug, 'topic_slug' => $thread->topic->slug, 'thread_slug' => $thread->slug]) }}" target="_blank">{{ $thread->title }}</a>
                            </h5>
                        </div>
                        <div class="col-2 pt-1">
                            <a href="{{ route('thread.unsubscribe', ['thread' => $thread->id, 'redirect=user.subscriptions']) }}" class="btn btn-warning btn-sm float-right">{{ __('Unsubscribe') }}</a>
                        </div>
                    </div>
                    @empty
                    {{ __('There are no subscribed threads to display.') }}
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
<!-- Thread Unsubscribe Form -->
<form id="threadUnsubscribeForm" action="{{ route('thread.unsubscribe', ['thread' => 0]) }}" method="post" class="d-none">
    @csrf
    <input name="redirect" type="hidden" value="user.subscriptions" />
    <input name="threads" type="hidden" value="" />
</form>
<script>
    var checked = false;
    var unsubscribed = [];
    var total = 0;

    function checkAll()
    {
        checked = !checked;
        unsubscribed = [];

        $('input:checkbox').each(function (index, value) {
            $(value).prop('checked', checked);

            if (checked) unsubscribed.push($(value).val());
        });

        $('#checkAllButton').text(checked ? "{{ __('Uncheck All') }}" : "{{ __('Check All') }}");
        $('#unsubscribeAllButton').prop('disabled', unsubscribed.length == 0);
        $('#unsubscribeAllButton span').text(unsubscribed.length);
    }

    function trackUnsubscribing(event)
    {
        let key = event.target.value;
        let pos = unsubscribed.indexOf(key);

        if (pos != -1) unsubscribed.splice(pos, 1); else unsubscribed.push(key);

        $('#unsubscribeAllButton').prop('disabled', unsubscribed.length == 0);
        $('#unsubscribeAllButton span').text(unsubscribed.length);

        if (unsubscribed.length == 0)
        {   
            checked = false;

            $('#checkAllButton').text("{{ __('Check All') }}");
        }
        else if (unsubscribed.length == total)
        {
            checked = true;

            $('#checkAllButton').text("{{ __('Uncheck All') }}");
        }
    }

    function unsubscribeMulti()
    {
        $('#threadUnsubscribeForm [name=threads]').val(unsubscribed.join(','));
        $('#threadUnsubscribeForm').submit()
    }

    window.addEventListener('DOMContentLoaded', (event) => {
        total = $('input:checkbox').length;

        $('#checkAllButton').prop('disabled', total == 0);
    });
</script>
@endsection
